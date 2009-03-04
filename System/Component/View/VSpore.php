<?php
/**
 * @package Bambus
 * @subpackage CommandQueries 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-28
 * @license GNU General Public License 3
 */
class VSpore extends BView
{
	/*
all active spore run
spore run content 
content access its spore 
content gets new link for changed values from spore
* 
array object => spore -> get my spore(bcontent)
array spore => object -> getTitle/Content/etc - tpl calls
* 
array objectid => spore
	spore->getObject 
*/
	const ACTIVE = 0,
		  INIT_CONTENT = 1,
		  ERROR_CONTENT = 2;
	private static $initialized = false;
	private static $spores = array();//class config data to create objects - serialize for save
	private static $active = array();//objects
	
	//name - to find itself in the configs
	private $name = null;
	
	//resolved content - BContent
	private $content = null;
	
	//current parameter settings - all
	private $query = null;

	//things set by setParameter()
	private $newParameters = array();
	
	//the alias to link to
	private $newMainParameter = '';
	
	private $target;
	
	//spores with names existing in $spores must not be created
	//names must be ascii, must not begin with "_" and must not contain "-"
	
	/**
	 * Init for static data
	 */
	private static function initialize()
	{
		if(!self::$initialized)
		{
			self::$initialized = true;
			try{
				self::$spores = DFileSystem::LoadData('./Content/QSpore/index.php');
			}
			catch (Exception $e)
			{
				self::$spores = array();
			}
		}	
	}
	
	/**
	 * init for object io - includes initialize()
	 */
	private static function raise()
	{
		self::initialize();
		foreach (self::$spores as $name => $conf) 
		{
			if(!empty($conf[self::ACTIVE]))
			{
				self::$active[$name] = new VSpore($name);
			}
		}
	}
	/**
	 * Save data - if it changed
	 */
	public static function Save()
	{
		self::initialize();
		try
		{
			DFileSystem::SaveData('./Content/QSpore/index.php', self::$spores);
			return true;
		}
		catch (Exception $e)
		{
			//@todo send notification
			return false;
		}
	}
	
	/**
	 * set a new spore layout
	 * @throws InvalidArgumentException
	 */
	public static function set($sporename, $active, $initContent, $errorContent)
	{
		self::initialize();
		if(preg_match('/(^([a-zA-Z0-9]+[a-zA-z0-9_]?|)$)/', $sporename))
		{
			self::$spores[$sporename] = array(
				!empty($active),
				$initContent,
				$errorContent
			);
		}
		else
		{
			throw new InvalidArgumentException('sporename invalid');
		}
	}
	/**
	 * Remove a spore by name
	 * @param string $sporename
	 */
	public static function remove($sporename)
	{
		self::initialize();
		if(isset(self::$spores[$sporename]))
		{
			unset(self::$spores[$sporename]);
		}
	}

	public static function exists($sporename)
	{
		self::initialize();
		return (isset(self::$spores[$sporename]));
	}
	
	/**
	 * get the spore layout
	 * array structure:
	 * name => array(bool active, string default-content-id, error-content-id)
	 * @return array $spores
	 */
	public static function getSpores()
	{
		self::initialize();
		return self::$spores;
	}
	
	/**
	 * Get all active spore names
	 * @return array
	 */
	public static function activeSpores()
	{
		self::initialize();
		$return = array();
		foreach (self::$spores as $name => $conf) 
		{
			if(!empty($conf[self::ACTIVE]))
			{
				$return[] = $name;
			}
		}
		return $return;
	}

	
	public static function sporeNames()
	{
		self::initialize();
		return array_keys(self::$spores);
	}
	
	/**
	 * Get a spore by name
	 * @param string $name
	 * @return VSpore|null
	 */
	public static function byName($name)
	{
		self::raise();
		if(isset(self::$active[$name]))
		{
			return self::$active[$name];
		}
		else
		{
			return null;
		}
	}
	
	private function loadContent()
	{
		if($this->content != null)
			return;
		$content = null;
		$alias = '';
		if(RURL::has($this->name))
		{
			$alias = RURL::get($this->name);
		}
		else
		{
			$alias = self::$spores[$this->name][self::INIT_CONTENT];
		}
		$content = SAlias::resolve($alias);
		
		if(!$content instanceof BContent)
		{
			$alias = self::$spores[$this->name][self::ERROR_CONTENT];
			$content = BContent::Open($alias);
		}
		$this->content = BContent::Access($content->Alias, $this);
		$this->content->InvokedByQueryObject($this);
		
		//do once
	}
	
	/**
	 * Get the assigned BContent object of this VSpore object
	 * Only works for active content 
	 * @return BContent
	 */
	public function getContent()
	{
		$this->loadContent();
		return $this->content;
	}
	
	public function getErrorContent()
	{
		$alias = self::$spores[$this->name][self::ERROR_CONTENT];
		return BContent::Open($alias);
	}
	
	public function hasContent()
	{
		$this->loadContent();
		return ($this->content !== null && $this->content instanceof BContent);
	}
	
	public static function isActive($sporename)
	{
		return isset(self::$spores[$sporename]) && self::$spores[$sporename][self::ACTIVE];
	}
	
	public function __get($var)
	{
		//forward to content object
		if($this->hasContent() && isset($this->content->{$var}))
		{
			return $this->content->{$var};
		}
	}
	
	public function __isset($var)
	{
		return ($this->hasContent() && isset($this->content->{$var}));
	}
	
	public function GetName()
	{
		return $this->name;
	}
	/**
	 * @param string $name
	 * @param boolean $active
	 * @param string $initContent
	 * @param string $errContent
	 * @throws Exception
	 */
	public function __construct($name)
	{
		if(isset(self::$spores[$name]))
		{
			$this->name = $name;
		}
		elseif($name != null)//null object wrapper for class fuinctions - tpl
		{
			throw new Exception('spore does not exist');
		}
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @param boolean $temporary
	 * @return VSpore
	 */
	public function SetLinkParameter($optionName, $value, $temporary = false)
	{
		if($value !== null)
		{
			//set new parameter
			$this->newParameters[(($temporary) ? '_' : '').$optionName] = $value;
			//remove (un-)temporary counterpart
			unset($this->newParameters[((!$temporary) ? '_' : '').$optionName]);
		}
		else
		{
			unset($this->newParameters['_'.$optionName]);
			unset($this->newParameters[$optionName]);
		}
		return $this;
	}
	/**
	 * Get the parameters set previously - read from querystring
	 *
	 * @param string $optionName
	 */
	public function GetParameter($optionName)
	{
		$val = '';
		if(RURL::has($this->name.'-'.$optionName))
		{
			$val = RURL::get($this->name.'-'.$optionName);
		}
		elseif(RURL::has('_'.$this->name.'-'.$optionName))
		{
			$val = RURL::get('_'.$this->name.'-'.$optionName);
		}
		return  $val;
	}
	
	public function LinkTo($target)
	{
		$this->target = $target;
		return $this;
	}
	
	public function __toString()
	{
		$dat = array(
			$this->name => $this->target
		);
		foreach ($this->newParameters as $name => $value) 
		{
			if(substr($name,0,1) == '_')
			{
				$dat['_'.$this->name.'-'.substr($name,1)] = $value;
			}
			else
			{
				$dat[$this->name.'-'.$name] = $value;
			}
		}
		return SLink::link($dat);
	}
	/* begin * * ITemplateSupporter**/
	
	// fx call(spore, prop)
	
	//Spore-name:foo -> VSpore:name:TemplateGet(foo)
	//Spore-name     -> VSpore:name:__toString()
	
	public function TemplateCallable($function = null)
	{
		$tplFunctions = array('LinkTo','GetParameter','GetName');
		if($function == null)
		{
			return $tplFunctions;
		}
		else
		{
			return in_array($function, $tplFunctions);
		}
	}
	
	public function TemplateCall($function, array $namedParameters)
	{
		if($this->TemplateCallable($function))
		{
			return call_user_func_array(array($this, $function), array_values($namedParameters));
		}
		else
		{
			return '';
		}
	}
	
	public function TemplateGet($property)
	{
		return $this->__get($property);
	}
	/* end * * ITemplateSupporter**/
	
}
?>