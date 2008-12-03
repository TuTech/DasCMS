<?php
/**
 * @package Bambus
 * @subpackage Navigators
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class NTreeNavigation 
    extends 
        BNavigation 
    implements 
        IShareable, 
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
	
	//IShareable
	const CLASS_NAME = 'NTreeNavigation';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @return NTreeNavigation
	 */
	public static function alloc()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
			try
		    {
				self::$index = DFileSystem::LoadData('./Content/'.self::CLASS_NAME.'/index.php');
			}
			catch (Exception $e)
			{
				self::$index = array();
			}
			
		}
		return self::$sharedInstance;
	}
    
	/**
	 * @return NTreeNavigation
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
	
    public static function set($nav,QSpore $spore, NTreeNavigationObject $tno_root)
    {
    	//nav names are fs-names
    	self::alloc();
    	self::$index[$nav] = array($spore->GetName(), $tno_root);
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
     * get the QSpore object of a nav
     *
     * @param string $nav
     * @return QSpore
     * @throws OutOfRangeException
     */
    public static function sporeOf($nav)
    {
    	if(!isset(self::$index[$nav]))
    	{
    		throw new OutOfRangeException();
    	}
    	if(QSpore::exists(self::$index[$nav][self::Spore]))
    	{
    		return new QSpore(self::$index[$nav][self::Spore]);
    	}
		else
		{
			$allSpores = QSpore::sporeNames();
			if(count($allSpores) == 0)
			{
				//no spores - create one
				QSpore::set($nav,true,null,null);
				QSpore::Save();
				return new QSpore($nav);
			}
			else
			{
				//the are some spore use whatever comes first
				return new QSpore($allSpores[0]);
			}
		}
    }
    
    public static function remove($nav)
    {
    	self::alloc();
    	if(self::exists($nav))
    	{
    		unset(self::$index[$nav]);
    	}
    }
    
    public static function exists($nav)
    {
    	self::alloc();
    	return array_key_exists($nav, self::$index);
    }
    
    public static function navigations()
    {
    	self::alloc();
    	return array_keys(self::$index);
    }
    
    //FIXME split nav data - one file per nav 
    
    public static function Save()
    {
    	self::alloc();
		DFileSystem::SaveData('./Content/'.self::CLASS_NAME.'/index.php', self::$index);
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
    		
    		if(QSpore::exists($sporeName) 
    			&& $root != null
    			&& $root instanceof NTreeNavigationObject)
    		{
    			if(QSpore::exists($sporeName) && QSpore::isActive($sporeName))
    			{
    				$spore = QSpore::byName($sporeName);
    				$navigation = new NTreeNavigationHelper($root,$spore);
    			}
    			else
    			{
    				$navigation = "<!-- spore not found or not active '".$sporeName."' -->";
    			}
    		}
    		$navigation = '<div id="Navigation-'.htmlentities($NavigationName, ENT_QUOTES, 'utf-8').'">'."\n".strval($navigation).'</div>'."\n";
    	}
    	return $navigation;
    }

/////////////////////////////////

    /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function TemplateProvidedFunctions()
    {
        return array('embed' => array('name','description' => 'embeds the Tree-Navigation with the given name'));
    }
    
    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function TemplateProvidedAttributes()
    {
        return array();
    }

    /**
	 * @param string $function
	 * @return boolean
	 */
	public function TemplateCallable($function)
	{
	    return $function == 'embed';
	}
	
	/**
	 * @param string $function
	 * @param array $namedParameters
	 * @return string in utf-8
	 */
	public function TemplateCall($function, array $namedParameters)
	{
	    if(!$this->TemplateCallable($function))
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
	public function TemplateGet($property)
	{
	    return '';
	}
}
?>