<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 29.07.2006
 * @license GNU General Public License 3
 */
class Application extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'Application';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
	    	$this->Gui = Gui::alloc();
	    	$this->FileSystem = FileSystem::alloc();
	    	$this->Linker = Linker::alloc();

	    	$this->Gui->init();
	    	$this->FileSystem->init();
	    	$this->Linker->init();
    	}
    }
  	//end IShareable
    
    
    
    var $name, $bad, $xml, $applicationDirectory, $Gui, $interfaceXML;
    var $initialized = false;
    var $tab = '';  
    
    function __construct()
    {
        parent::Bambus();
        $this->initialized = false;
    }
    
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
    function loadVars($get,$post,$session,$uploadfiles)
    {
		//init program
		if(!$this->initialized)
		{
    	    parent::loadVars($get,$post,$session,$uploadfiles);
        	$this->autorun();
		}
		else
		{
			$this->get = array_merge($this->get, $get);
			$this->post = array_merge($this->post, $post);
			$this->session = array_merge($this->session, $session);
			$this->files = array_merge($this->files, $uploadfiles);
		}
    }
			
//////////////////////////////////////////////////////////////
//// Class functions
//////////////////////////////////////////////////////////////
	
	function autorun()
	{
		if($this->initApp())
        {//application valid
        	$get = &$this->get;
			define('BAMBUS_XML_NODE_VALUE', 0);
			define('BAMBUS_XML_NODE_ATTRIBUTES', 1);
        	        	
        	//set the active tab
        	$tabs = $this->getXMLPathValueAndAttributes('bambus/tabs/tab');
        	if(!isset($tabs[0]))
        	{//no tabs in xml
        		//create default "edit"-tab
        		$tabs[0] = array('edit', array('icon' => 'edit'));
        	}
        	$availableTabs = array();
        	foreach($tabs as $tab)
        	{
        		$availableTabs[] = $tab[0];
        	}
			if(empty($get['tab']) || !in_array($get['tab'], $availableTabs))
			{
				$this->tab = $tabs[0][0];
			}
			else
			{
				$this->tab = $get['tab'];
			}
        }
	}

	function controller()
	{
    	//load controll php
    	$valueAndAttributes = $this->getXMLPathValueAndAttributes('bambus/application/controller');
    	$path = BAMBUS_APPLICATION_DIRECTORY;
    	if(!empty($valueAndAttributes[0][0]))
    	{
        	return($path.$valueAndAttributes[0][0]);
    	}
    	return false;
	}
		
	function run()
	{
		$applicationNode = $this->getXMLNodeByPathAndAttribute('bambus/application/interface', 'name',$this->tab);
    	$path = BAMBUS_APPLICATION_DIRECTORY;
		if(!empty($applicationNode[1]['src']))
		{
			//generate gui by php
			return $path.$applicationNode[1]['src'];
		}
		return false;
	}

	function generateTaskBar()
	{//TASK- not Tab-Bar
		$get = &$this->get;
		$html = '';
		$applicationNode = $this->getXMLNodeByPathAndAttribute('bambus/application/interface', 'name', $this->tab);
		if(!empty($applicationNode[0]))
		{
			if(!empty($applicationNode[1]['search']))
			{
				$html .= $this->Gui->search($applicationNode[1]['search']);
			}
			$tasks = $this->getSCTagValues('task', $applicationNode[0]);
			$html .= $this->Gui->beginTaskBar();
			$first = true;
			$closed = true;
			$panelName = '';
			$CommandBar = '<div class="CommandBar">';
			foreach($tasks as $task)
			{
				switch($task['type'])
				{
					case('spacer'):
						$panelName = isset($task['name']) ?  SLocalization::get($task['name']) : '';
						if(!$closed)
						{
							$CommandBar .= '</tr></table>';
							$closed = true;
						}
						$html .= $this->Gui->taskSpacer();
						break;
					case('item'):
						
						$html .= $this->Gui->taskSpacer();
						break;
					case('button'):
						$doJS = (empty($task['mode']) || strtolower($task['mode']) == 'javascript');
						$hotkey = (!empty($task['hotkey'])) ? $task['hotkey'] : '';
						if($doJS)
						{
							$action = $task['action'];
							if(!empty($task['confirm']))
							{
								$action = sprintf("if(confirm('%s')){%s}", utf8_encode(html_entity_decode(SLocalization::get($task['confirm']))), $action);
							}
						}
						else
						{
							$action = '';
							if(!empty($task['confirm']))
							{
								$action = sprintf("if(confirm('%s'))", utf8_encode(html_entity_decode(SLocalization::get($task['confirm']))));
							}
							$prompt = '';
							if(!empty($task['prompt']))
							{
								$prompt = "+'&amp;prompt='+prompt('".addslashes($task['prompt'])."')";
							}
							$action .= sprintf("{top.location = '%s'%s;}", addslashes(parent::createQueryString(array('_action' => $task['action']))), $prompt);
						}
						$html .= $this->Gui->taskButton($action, $doJS, $task['icon'], SLocalization::get($task['caption']),$hotkey);
						if($closed)
						{
							$CommandBar .= sprintf(
								'<table cellspacing="0" id="%s" class="CommandBarPanel" title="%s"><tr><th class="CommandBarPanelStart"></th>'
								,$panelName
								,htmlentities($panelName, ENT_QUOTES, 'UTF-8')
							);
							$closed = false;
						}						
						$CommandBar .= sprintf(
							'<td><a class="CommandBarPanelItem" title="%s" href="javascript:%s">%s</a></td>'
							,SLocalization::get($task['caption'])
							,$action
							,new WIcon($task['icon'], SLocalization::get($task['caption']))
						);
						break;
				}
				$first = false;
			}
			if(!$closed)
			{
				$CommandBar .= '</tr></table>';
			}
			$CommandBar .= '<br class="CommandBarTerminator" /></div>';
			$html .= $this->Gui->endTaskBar();
		}
		return $CommandBar;
	}
		
	function initApp()
	{
    	$path = BAMBUS_APPLICATION_DIRECTORY;
		$xmlfile = $path.'Application.xml';
		if(file_exists($xmlfile))
		{
			$this->xml = $this->FileSystem->read($xmlfile);
			$this->initialized = true;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function getSCTagValues($tagName, $xml = false)
	{
		if($xml == false)
		{
			$xml = $this->xml;
		}
		$preg = array();
		preg_match_all("/<".$tagName."([^>]*)\/>/muiU", $xml, $preg);
		$nodes = array();
		for($i = 0; $i < count($preg[0]); $i++)
		{
			$attributes =  (isset($preg[1][$i])) ? $preg[1][$i] : '';
			if(empty($attributes))
			{
				$attributelist = array();
			}
			else
			{		
				$kpreg = array();
				$attributelist = array();
				preg_match_all("/([\\w]+)[\\s]*=[\\s]*\"([^\"]+)\"/muiU", $attributes, $kpreg);
				for($k = 0; $k < count($kpreg[0]);$k++)
				{
					$attributelist[$kpreg[1][$k]] = $kpreg[2][$k];
				}
			}
			$nodes[$i] = $attributelist;
		}
		return $nodes;
	}
	
	function getXMLNodeByPathAndAttribute($path, $attribute, $attributeQuery, $xml = false)
	{
		if($xml == false)
		{
			$xml = $this->xml;
		}
		$nodes = $this->getXMLPathValueAndAttributes($path, $xml);
		$i = 0;
		while(isset($nodes[$i]))
		{
			if(isset($nodes[$i][1][$attribute]) && $nodes[$i][1][$attribute] == $attributeQuery)
			{
		//FOUND! >>STOP HERE<<
				return $nodes[$i];
			}
			$i++;
		}
		return false;
	}
	
	function arrayFromAttributeString($attributes)
	{
		$attributelist = array();
		$kpreg = array();
		preg_match_all("/([\\w]+)[\\s]*=[\\s]*\"([^\"]+)\"/muiU", $attributes, $kpreg);
		for($k = 0; $k < count($kpreg[0]);$k++)
		{
			$attributelist[$kpreg[1][$k]] = $kpreg[2][$k];
		}
		return $attributelist;
	}
	
	function getXMLPathValueAndAttributes($path, $xml = false)
	{
		if($xml == false)
		{
			$xml = $this->xml;
		}
		$stages = explode('/', $path);
		while(count($stages) > 1)
		{
			$preg = array();
			preg_match("/<".$stages[0]."[^>]*>([^\\0]*)<\/".$stages[0].">/mui", $xml, $preg);
			$xml = (isset($preg[1])) ? $preg[1] : ''; 
			unset($stages[0]);
			$stages = array_values($stages);
		}
		$preg = array();
		preg_match_all("/<".$stages[0]."([^>]*)>([^\\0]*)<\/".$stages[0].">/muiU", $xml, $preg);
		$nodes = array();
		for($i = 0; $i < count($preg[0]); $i++)
		{
			
			$nodevalue = (isset($preg[2][$i])) ? $preg[2][$i] : '';
			$attributes =  (isset($preg[1][$i])) ? $preg[1][$i] : '';
			if(empty($attributes))
			{
				$attributelist = array();
			}
			else
			{		
				$attributelist = $this->arrayFromAttributeString($attributes);
			}
			$nodes[$i] = array($nodevalue, $attributelist);
		}
		return $nodes;
	}
}
?>
