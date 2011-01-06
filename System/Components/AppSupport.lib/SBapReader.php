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
    implements 
        Interface_Singleton 
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
		$requests = array_intersect($requests, array('name', 'description', 'icon', 'priority', 'version', 'purpose', 'tabs', 'guid'));
		if(substr($appDefinition,-16) != '/Application.xml')
		{
			$appDefinition .= '/Application.xml';
		}
		if(file_exists($appDefinition))
		{
			$xml = Core::FileSystem()->load(realpath($appDefinition));
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
				if($tag == 'guid')
				{
					preg_match("/<appController[\\s]+guid=\"([a-zA-Z0-9-_\.]+)\">(.*)<\/appController>/", $xml, $preg);
					$result[$tag] = (isset($preg[1])) ? $preg[1] : '';
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
	
	//Interface_Singleton
	const CLASS_NAME = 'SBapReader';
	public static $sharedInstance = NULL;
	/**
	 * @return SBapReader
	 */
	public static function getInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end Interface_Singleton
}
?>