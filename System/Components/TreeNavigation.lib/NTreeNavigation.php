<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Navigator
 */
class NTreeNavigation 
    implements 
        Interface_Singleton, 
        ITemplateSupporter, 
        IGlobalUniqueId
{
    const GUID = 'org.bambuscms.navigation.treenavigation';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    //constants
	const Spore = 0;
	const Tree = 1;
	
	//static vars
	private static $index;
	
	//object vars
	public $spore = null;
	private $NodeData = array();
	private $ActiveNodes = array();
	
	//Interface_Singleton
	const CLASS_NAME = 'NTreeNavigation';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @return NTreeNavigation
	 */
	public static function getInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
			try
		    {
				self::$index = Core::FileSystem()->loadEncodedData('./Content/'.self::CLASS_NAME.'/index.php');
			}
			catch (Exception $e)
			{
				self::$index = array();
			}
			
		}
		return self::$sharedInstance;
	}
	//end Interface_Singleton
	
    public static function set($nav,Controller_View_Content $spore, NTreeNavigationObject $tno_root)
    {
    	//nav names are fs-names
    	self::getInstance();
    	self::$index[$nav] = array($spore->getName(), $tno_root);
    }
    /**
     * get the root element
     *
     * @return NTreeNavigationObject
     * @throws OutOfRangeException
     */
    public static function getRoot($nav)
    {
        if(!isset(self::$index[$nav]))
    	{
    		throw new OutOfRangeException();
    	}
    	return self::$index[$nav][self::Tree];
    }
    
    /**
     * get the Controller_View_Content object of a nav
     *
     * @param string $nav
     * @return Controller_View_Content
     * @throws OutOfRangeException
     */
    public static function sporeOf($nav)
    {
    	if(!isset(self::$index[$nav]))
    	{
    		throw new OutOfRangeException();
    	}
    	if(Controller_View_Content::exists(self::$index[$nav][self::Spore]))
    	{
    		return new Controller_View_Content(self::$index[$nav][self::Spore]);
    	}
		else
		{
			$allSpores = Controller_View_Content::sporeNames();
			if(count($allSpores) == 0)
			{
				//no spores - create one
				Controller_View_Content::set($nav,true,null,null);
				Controller_View_Content::save();
				return new Controller_View_Content($nav);
			}
			else
			{
				//the are some spore use whatever comes first
				return new Controller_View_Content($allSpores[0]);
			}
		}
    }
    
    public static function remove($nav)
    {
    	self::getInstance();
    	if(self::exists($nav))
    	{
    		unset(self::$index[$nav]);
    	}
    }
    
    public static function exists($nav)
    {
    	self::getInstance();
    	return array_key_exists($nav, self::$index);
    }
    
    public static function navigations()
    {
    	self::getInstance();
    	return array_keys(self::$index);
    }
    
    //FIXME split nav data - one file per nav 
    
    public static function save()
    {
    	self::getInstance();
		Core::FileSystem()->storeDataEncoded('./Content/'.self::CLASS_NAME.'/index.php', self::$index);
		return true;
    }
    
    public static function navigatieWith($NavigationName)
    {
    	//exists nav
    	$navigation = '';
    	if(self::exists($NavigationName))
    	{
    		$sporeName = self::$index[$NavigationName][self::Spore];
    		$root = self::$index[$NavigationName][self::Tree];
    		
    		if(Controller_View_Content::exists($sporeName) 
    			&& $root != null
    			&& $root instanceof NTreeNavigationObject)
    		{
    			if(Controller_View_Content::exists($sporeName) && Controller_View_Content::isActive($sporeName))
    			{
    				$spore = Controller_View_Content::byName($sporeName);
    				$navigation = new NTreeNavigationHelper($root,$spore);
    			}
    			else
    			{
    				$navigation = "<!-- spore not found or not active '".$sporeName."' -->";
    			}
    		}
    		$navigation = sprintf(
    			"<div id=\"Navigation-%s\">%s</div>\n"
    		    ,String::htmlEncode($NavigationName)
    		    ,strval($navigation)
		    );
    	}
    	return $navigation;
    }

/////////////////////////////////

    /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function templateProvidedFunctions()
    {
        return array('embed' => array('name','description' => 'embeds the Tree-Navigation with the given name'));
    }
    
    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function templateProvidedAttributes()
    {
        return array();
    }

    /**
	 * @param string $function
	 * @return boolean
	 */
	public function templateCallable($function)
	{
	    return $function == 'embed';
	}
	
	/**
	 * @param string $function
	 * @param array $namedParameters
	 * @return string in utf-8
	 */
	public function templateCall($function, array $namedParameters)
	{
	    if(!$this->templateCallable($function))
	    {
	        throw new XTemplateException('called undefined function');
	    }
	    if(!array_key_exists('name', $namedParameters))
	    {
	        throw new XArgumentException('name must be defined');
	    }
	    if(self::exists($namedParameters['name']))
	    {
	        return self::navigatieWith($namedParameters['name']);
	    }
	    else
	    {
	        return '';
	    }
	}
	
	/**
	 * @param string $property
	 * @return string in utf-8
	 */
	public function templateGet($property)
	{
	    return '';
	}
}
?>