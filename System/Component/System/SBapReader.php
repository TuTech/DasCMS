<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SBapReader 
    extends 
        BSystem 
    implements 
        IShareable 
{
	/**
	 * return an array of reuested data
	 * request can contain name, description, icon, priority, version, tabs
	 * @todo Rewrite with proper xml 
	 * @param string $appDefinition
	 * @param array $requests
	 */
	public static function getAttributesOf($appDefinition, $requests = array())
	{
		$result = array();
		$requests = array_intersect($requests, array('name', 'description', 'icon', 'priority', 'version', 'purpose', 'tabs'));
		if(substr($appDefinition,-16) != '/Application.xml')
		{
			$appDefinition .= '/Application.xml';
		}
		if(file_exists($appDefinition))
		{
			$xml = DFileSystem::Load($appDefinition);
			foreach ($requests as $tag) 
			{
				if($tag == 'tabs')
				{
					preg_match_all("/<tab[\\s]+icon=\"([a-zA-Z0-9-_]+)\">(.*)<\\/tab>/", $xml, $matches);
			    	for($i = 0; $i < count($matches[0]); $i++)
			    	{
			    		//tabs => [tab-name => icon]
			    		$result['tabs'][$matches[2][$i]] = $matches[1][$i];
			    	}
				}
				else
				{
					preg_match("/<".$tag.">(.*)<\/".$tag.">/", $xml, $preg);
					$result[$tag] = (isset($preg[1])) ? $preg[1] : '';
				}
			}
		}
		else
		{
			throw new XFileNotFoundException($appDefinition);
		}
		return $result;
	}
	
	public function listAvailable()
	{
		$available = array();
		$appPath = SPath::SYSTEM_APPLICATIONS;
		$dirhdl = opendir($appPath);
		while($item = readdir($dirhdl))
		{
			if(is_dir($appPath.$item) 
				&& substr($item,0,1) != '.' 
				&& strtolower(substr($item,-4)) == '.bap' 
				&& file_exists($appPath.$item.'/Application.xml')
			)
			{
				$data = self::getAttributesOf($appPath.$item, array('name', 'description', 'icon', 'tabs'));
				$app = substr($item,0,((strlen(DFileSystem::suffix($item))+1) * -1));
				//FIXME use app ctrl GUID
				if(PAuthorisation::has('org.bambusms.application.'.strtolower($app)))
				{
					$available[$item] = array(
						 'name' => $data['name']
						,'desc' => $data['description']
						,'icon' => $data['icon']
						,'tabs' => $data['tabs']
						,'active' => false
						);
				}
			}
		}
		closedir($dirhdl);
		
		$selectedApp = RURL::get('editor');
		if(!empty($selectedApp) && isset($available[$selectedApp]))
		{
			//select tab
			$selectedTab = RURL::get('tab');
			//correct if necessary
			if(!array_key_exists($selectedTab, $available[$selectedApp]['tabs']))
			{
				//right app, wrong tab
				$tabs = array_keys($available[$selectedApp]['tabs']);
				if(count($tabs) > 0)
				{
					$selectedTab = $tabs[0];
				}
			}
			//prevent failure if no tabs exists
			if(array_key_exists($selectedTab, $available[$selectedApp]['tabs']))
			{
				$available[$selectedApp]['active'] = $selectedTab;
			}
		}
		return $available;
	}
	
	
	//IShareable
	const CLASS_NAME = 'SBapReader';
	public static $sharedInstance = NULL;
	/**
	 * @return SBapReader
	 */
	public static function getSharedInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
	/**
	 * @return SBapReader
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
}
?>