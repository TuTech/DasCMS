<?php
class API_Controller_Apps 
	extends _API_Controller_Array
	implements 
		API_Interface_EntryPoint,
		API_Interface_AcceptsGet
{
	const APP_INTERFACE = 'Application_Interface_AppController';

	public function __construct() {
		if(!PAuthorisation::has('org.bambuscms.login'))
		{
			throw new Exception_HTTP(403);
		}
	}

	protected function getKeys() {
		return array('guid', 'title', 'icon', 'description', 'type');
	}

	public function resolveSubPath(array $path) {
		$responder = null;
		//we are the requested path
		if(count($path) == 0){
			$responder = $this;
		}
		else{
			//get load sub path
			$appGUID = array_shift($path);
			//resolve guid -> app class
			$app = BObject::resolveGUID($appGUID);

			//check if request is an app
			if(!Core::isImplementation($app, self::APP_INTERFACE)){
				throw new XUndefinedIndexException('not an app');
			}

			//load app controller
			$appCtrl = BObject::InvokeObjectByDynClass($app);

			//create sub component
			$subComponent = new API_Controller_AppInfo($appCtrl);
			$responder = $subComponent->resolveSubPath($path);
		}
		return $responder;
	}

	protected function getElements() {
		//get Controller_Application classes
		$elements = array();
		$apps = Core::getClassesWithInterface(self::APP_INTERFACE);
		foreach ($apps as $app){
			$appController = new $app();
			if ($appController instanceof Application_Interface_AppController
					&& PAuthorisation::has($appController->getClassGUID()))
			{
				$elements[] = array(
					$appController->getClassGUID(),
					$appController->getTitle(),
					$appController->getIcon(),
					$appController->getDescription(),
					$appController->getEditor()
				);
			}
		}
		return $elements;
	}

	public function getControllerName(){
		return 'apps';
	}
}
?>