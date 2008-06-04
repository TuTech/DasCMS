<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class SConfiguration extends BSystem implements IShareable
{
	//IShareable
	private static $initDone = false;
	const Class_Name = 'SConfiguration';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
			self::$sharedInstance = new $class();
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initDone)
    	{
    		foreach(array('data', 'options', 'defaults', 'typeof') as $var)
    		{
	    		try
	    		{
			    	self::${$var} = DFileSystem::LoadData($this->StoragePath($var));
	    		}
	    		catch (Exception $e)
	    		{
	    			self::${$var} = array();
	    			//echo $e->getMessage().'<br />';
	    		}
    		}
    		self::$initDone = true;
    	}
    	return $this;
    }
	//end IShareable

    public function __destruct()
    {
//    	echo '__destruct';
    	if(self::$update)
    	{
    		foreach(array('data', 'options', 'defaults', 'typeof') as $var)
    		{
	    		try
	    		{
//	    			echo 'update '.$this->StoragePath($var).'<br />';
//	    			var_dump(self::${$var});
	    			DFileSystem::SaveData($this->StoragePath($var), self::${$var});
	    		}
	    		catch (Exception $e)
	    		{
	    			echo $e->getMessage().'<br />';
	    		}
    		}
    	}
    }
    /**
     * Possible data types for config values
     * @var array
     */
    private static $types = array(
    	0 => 'bool',
    	1 => 'string',
    	2 => 'int',
    	3 => 'float',
    	4 => 'implementation',
    	5 => 'extend'
    );
    
	/**
     * Configuration key to data type association 
     * key => type-index
     * @var array
     */
    private static $typeof = array(); 
    
    /**
     * The configuration values
     * key => data
     * @var array
     */
    private static $data = array();
    
    /**
     * Possble options for the configuration key
     * - Alternative values 
     * - Interface to be implemented by configuration value
     * - Class to be extended by configuration value
     * key => value|array
     * @var array 
     */
    private static $options = array();
    
    /**
     * Defaults will be used if data value is null
     * key => value
     * @var array
     */
    private static $defaults = array();
    
    /**
     * update data file in __destruct?
     * @var bool
     */
    private static $update = false;
    
    //bool,string,double,integer,implementing,extending
    
    /**
     * Get value for a configuration key
     *
     * @param string $key
     * @return mixed
     */
    public function Get($key)
    {
    	//load data file
    	return (isset(self::$data[$key])) 
    		? self::$data[$key] 
    		: ((isset(self::$defaults[$key])) 
	    		? self::$defaults[$key] 
	    		: '');
    }
	
    /**
     * Query multiple keys from config matching given regexp
     *
     * @param string $keyRegexp
     * @return array 
     */
    public function Query($keyRegexp)
    {
    	//load data file
    	//foreach array keys do regexp
    	$result = array();
    	foreach (self::$data as $key => $value) 
    	{
    		if(preg_match($keyRegexp, $key))
    		{
    			$result[$key] = $value;
    		}
    	}
    	return $result;
    }
    
    private static function checkOptionsApply($key, $value)
    {
    	return self::$options[$key] == null || in_array($value, self::$options[$key]);
    }
    
    /**
     * Set an existing key to a new value
     *
     * @param string $key
     * @param mixed $value
     * @return bool success
     * @todo add throws
     */
    public function Set($key, $value)
    {
    	//load all files
    	if(isset(self::$typeof[$key]))
    	{
    		switch(self::$types[self::$typeof[$key]])
    		{
		    	case  'bool':
		    		if(is_bool($value))
		    		{	
		    			self::$data[$key] = $value;
		    			self::$update = true;
		    		}
		    	case  'string':
		    		//check options
		    		//if(is_bool($value))
		    		if(self::checkOptionsApply($key, $value))
		    		{	
		    			self::$data[$key] = $value;
		    			self::$update = true;
		    		}
		    		
		     	case  'int':
		     		//check options
		     		if(is_numeric($value) && self::checkOptionsApply($key, $value))
		     		{
		     			self::$data[$key] = round($value);
		     			self::$update = true;
		     		}
		    	case  'float':
		     		//check options
		     		if(is_float($value) && self::checkOptionsApply($key, $value))
		     		{
		     			self::$data[$key] = $value;
		     			self::$update = true;
		     		}
		    	case  'implementation':
		     		if(in_array(self::$options[$key], class_implements($value, true)))
		     		{
		     			self::$data[$key] = $value;
		     			self::$update = true;
		     		}
		    		
		    	case  'extend':
		    		$SCI = SComponentIndex::alloc()->init();
		     		if($SCI->IsExtension($value, self::$options[$key]))
		     		{
		     			self::$data[$key] = $value;
		     			self::$update = true;
		     		}
		    		
		    	default:
		    		return false;
    		}
    	}
    	else
    	{
    		throw new XUndefinedIndexException('key not in configuration');
    	}
    }
    /**
     * Get the type of a configuration key
     *
     * @param string $key
     * @return string type | null
     */
    public function TypeOf($key)
    {
    	//load type file
    	return (isset(self::$typeof[$key])) ? self::$types[$typeof[$key]] : null;
    }
    
    /**
     * values that can be selected for the $key
     *
     * @param string $key
     * @return array
     */
    public function OptionsFor($key)
    {
    	//TODO entend & interface workaround
    	$t = self::$types[self::$typeof[$key]];
    	if($t == 'implementation')
    	{
    		//query class db
    		$SCI = SComponentIndex::alloc()->init();
    		return $SCI->ImplementationsOf(self::$options[$key]);
    	}
    	elseif($t == 'extend')
    	{
    		//query class db
    		$SCI = SComponentIndex::alloc()->init();
    		return $SCI->ExtensionsOf(self::$options[$key]);
    	}
    	else
    	{
	    	//load options& type file
	    	return (isset(self::$options[$key])) ? self::$options[$key] : array();
    	}
    }
    
    /**
     * Create a new configuration key
     *
     * @param string $key
     * @param string $type
     * @param string $defaultValue
     * @param array $options
     * @todo add throws
     */
	public function Create($key, $type, $defaultValue = null, $options = null)
	{
		//load all files
		if(isset(self::$data[$key]))
		{	
			throw new Exception('key_exists',0);
		}
		if(!in_array($type, self::$types))
		{
			throw new Exception('unknown_type',1);
		}
		if($type == 'bool' && !empty($options))
		{
			throw new Exception('argument_failure',2);
		}
		if(($type == 'implementation' || $type == 'extend') && (empty($options) || !is_string($options)))
		{
			throw new Exception('argument_failure',2);
		}
		self::$data[$key] = null;
		self::$typeof[$key] = array_search($type, self::$types);
		if($defaultValue != null)	
			self::$defaults[$key] = $defaultValue;
		if($options != null)	
			self::$options[$key] = $options;
		self::$update = true;
	}
}
?>