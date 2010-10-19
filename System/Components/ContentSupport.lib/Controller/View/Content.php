<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage View
 */
class Controller_View_Content extends BView
{
	const ACTIVE = 0,
		  INIT_CONTENT = 1,
		  ERROR_CONTENT = 2;
	private static $initialized = false;
	private static $spores = array();//class config data to create objects - serialize for save
	private static $active = array();//objects
	private static $toDelete = array();
	//name - to find itself in the configs
	private $name = null;
	
	//resolved content - Interface_Content
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
				$res = Core::Database()
					->createQueryForClass('Controller_View_Content')
					->call('loadSpores')
					->withoutParameters();
				self::$spores = array();
				while ($row = $res->fetchResult())
				{
				    self::$spores[$row[0]] = array(
				        self::ACTIVE => $row[1] == 'Y',
				        self::INIT_CONTENT => $row[2],
				        self::ERROR_CONTENT => $row[3],
			        );
				}
				$res->free();
			}
			catch (Exception $e)
			{
				echo $e->getTraceAsString();
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
				self::$active[$name] = new Controller_View_Content($name);
			}
		}
	}
	/**
	 * Save data - if it changed
	 */
	public static function save()
	{
		self::initialize();
		$DB = DSQL::getInstance();
		$DB->beginTransaction();
		try
		{
			foreach (self::$toDelete as $name)
			{
				Core::Database()
					->createQueryForClass('Controller_View_Content')
					->call('deleteSpore')
					->withParameters($name)
					->execute();
			}
			foreach (self::$spores as $name => $data){
				//init 
				$q = Core::Database()
					->createQueryForClass('Controller_View_Content');
				$def = $data[Controller_View_Content::INIT_CONTENT];
				$err = $data[Controller_View_Content::ERROR_CONTENT];
				$noDef = empty($def);
				$noErr = empty($err);
				$active = $data[Controller_View_Content::ACTIVE] ? 'Y' : 'N';

				if($noDef && $noErr){
					$q->call('setSpore')
						->withParameters($name, $active, $active)
						->execute();
				}
				elseif($noErr){
					$q->call('setSporeWDef')
						->withParameters($name, $active, $def, $active, $def)
						->execute();
				}
				elseif($noDef){
					$q->call('setSporeWErr')
						->withParameters($name, $active, $err, $active, $err)
						->execute();
				}
				else{
					$q->call('setSporeWDefWErr')
						->withParameters($name, $active, $def, $err, $active, $def, $err)
						->execute();
				}
			}
			$DB->commit();
			return true;
		}
		catch (Exception $e)
		{
			$DB->rollback();
			SNotificationCenter::report('warning', 'spores_not_saved');
			echo $e->getTraceAsString();
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
		self::$toDelete[] = $sporename; 
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
	 * @return Controller_View_Content|null
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
		$content = Controller_Content::getInstance()->tryOpenContent($alias);
		
		if($content instanceof CError)
		{
			$alias = self::$spores[$this->name][self::ERROR_CONTENT];
			$content = Controller_Content::getInstance()->tryOpenContent($alias);
		}
		$this->content = Controller_Content::getInstance()->accessContent($content->Alias, $this);
		$this->content->setParentView($this);
		
		//do once
	}
	
	/**
	 * Get the assigned Interface_Content object of this Controller_View_Content object
	 * Only works for active content 
	 * @return Interface_Content
	 */
	public function getContent()
	{
		$this->loadContent();
		return $this->content;
	}
	
	public function getErrorContent()
	{
		$alias = self::$spores[$this->name][self::ERROR_CONTENT];
		return Controller_Content::getInstance()->tryOpenContent($alias);
	}
	
	public function hasContent()
	{
		$this->loadContent();
		return ($this->content !== null && $this->content instanceof Interface_Content);
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
	 * @return Controller_View_Content
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
	public function GetParameter($optionName, $encoding = null)
	{
		$val = '';
		if(RURL::has($this->name.'-'.$optionName))
		{
			$val = RURL::get($this->name.'-'.$optionName, $encoding);
		}
		elseif(RURL::has('_'.$this->name.'-'.$optionName))
		{
			$val = RURL::get('_'.$this->name.'-'.$optionName, $encoding);
		}
		return  $val;
	}
	
	public function LinkTo($target)
	{
		$this->target = $target;
		return $this;
	}
	
	public function buildParameterName($param)
	{
	    if(substr($param,0,1) == '_')
		{
			$param = '_'.$this->name.'-'.substr($param,1);
		}
		else
		{
			$param = $this->name.'-'.$param;
		}
		return $param;
	}
	
	public function __toString()
	{
		$dat = array(
			$this->name => $this->target
		);
		foreach ($this->newParameters as $name => $value) 
		{
			$dat[$this->buildParameterName($name)] = $value;
		}
		return SLink::link($dat);
	}
	/* begin * * ITemplateSupporter**/
	
	// fx call(spore, prop)
	
	//Spore-name:foo -> Controller_View_Content:name:templateGet(foo)
	//Spore-name     -> Controller_View_Content:name:__toString()
	
	public function templateCallable($function = null)
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
	
	public function templateCall($function, array $namedParameters)
	{
		if($this->templateCallable($function))
		{
			return call_user_func_array(array($this, $function), array_values($namedParameters));
		}
		else
		{
			return '';
		}
	}
	
	public function templateGet($property)
	{
		return $this->__get($property);
	}
	/* end * * ITemplateSupporter**/
	
}
?>