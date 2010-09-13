<?php
//this is the setup for the main setup
//setup for the planned subinstances should
//be handled in something like SubsystemSetup
class SystemSetup
{
	private $setupObjects = array();
	private $continueSetupFile = 'Content/allow_continue_setup.txt';

	/**
	 * create a folder and check permissions
	 * @param string $folder
	 * @return bool
	 */
	protected function createAndCheck($folder){
		$res = mkdir($folder) && is_readable($folder) && is_writable($folder);
		if(!$res){
			header('Internal Server Error', true, 500);
		}
		return $res;
	}

	/**
	 * check env
	 */
	public function __construct(){
		//check for the content folder and fail if it exists
		if(file_exists('Content') && !file_exists($this->continueSetupFile)){
			header('Locked', true, 423);
			die('Content folder already exists');
		}
		if(version_compare('5.2', PHP_VERSION, '>')){
			header('Internal Server Error', true, 500);
			die('PHP 5.2 or better required');
		}
		if(!is_writable('.')){
			header('Internal Server Error', true, 500);
			die('Setup is not allowed to write');
		}
		if(ini_get('safe_mode')){
			header('Internal Server Error', true, 500);
			die('PHP safe_mode is active. The setup can\'t do it\'s work with this setting');
		}
	}

	/**
	 * run setup staged
	 * @param string $firstStage
	 * @param string ...
	 */
	protected function runStages($firstStage){
		foreach (func_get_args() as $stage){
			$interface = 'Setup_For'.$stage;
			foreach ($this->setupObjects as $class => $object){
				if($object instanceof $interface){
					$object->{'run'.$stage.'Setup'}();
				}
			}
		}
	}

	/**
	 * run setup 
	 * @param array $inputData
	 */
	public function run(array $inputData){
		if(!file_exists($this->continueSetupFile)){
			//*create content folder
			$this->createAndCheck('Content') || die('Creation of the main folder "Content" failed');
			foreach (array('ClassCache', 'SQLCache', 'temp', 'logs','configuration') as $folder){
				$this->createAndCheck('Content/'.$folder) || die('Could not create subfolder '.$folder);
			}
		}
		
		//*run buildIndex
		//create index of all classes
		CoreUpdate::run(CoreUpdate::NO_DATABASE);

		//load all setup objects
		$classes = Core::getClassesWithInterface('Setup_Component');
		$failedConditions = array();
		foreach ($classes as $class){
			$object = new $class;
			if($object instanceof Setup_Component){
				$object->setContentFolder(getcwd().'/Content/');
				$object->setInputData($inputData);
				$messages = $object->validateInputData();
				foreach ($messages as $message){
					$failedConditions[] = $message;
				}
				$this->setupObjects[$class] = $object;
			}
		}
		if(count($failedConditions) > 0){
			$txt = '';
			foreach ($failedConditions as $message){
				$txt .= implode("\t", $message)."\n";
			}
			Core::dataToFile($txt, $this->continueSetupFile);
			header("Precondition Failed", true, 412);
			die(json_encode($failedConditions));
		}
		ob_start();
		//*run <Setup_Component>ContentFolder
		//*run <Setup_Component>Configuration
		$this->runStages(
				'ContentFolder',
				'Configuration'
			);
		
		//*run buildSQL
		//compile sql queries e.g. fill in table prefixes
		CoreSQLUpdate::run();

		//*run buildCacheManifest
		CoreManagementUpdate::run();
		
		//*run <Setup_Component>DatabaseTables
		//*run <Setup_Component>DatabaseTableReferences
		//*run <Setup_Component>DatabaseContent
		//*run <Setup_Component>Content
		$this->runStages(
				'DatabaseTables',
				'DatabaseTableReferences',
				'DatabaseContent',
				'Content'
			);
		//create index of all classes and write them in the database
		CoreUpdate::run(CoreUpdate::WITH_DATABASE);

		if(file_exists($this->continueSetupFile)){
			unlink($this->continueSetupFile);
		}
		header('Created', true, 201);
		$out = ob_get_contents();
		ob_end_clean();
		print("Setup successful\n");
		print($out);
	}
}
?>
