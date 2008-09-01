<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 24.01.2007
 * @license GNU General Public License 3
 */
class BCMSString extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'BCMSString';
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
			$this->Configuration = Configuration::alloc();
			//$this->Navigations = Navigations::alloc();

			$this->Configuration->init();
			//$this->Navigations->init();
    	}
    }
	//end IShareable

    var $env = array();
    var $cms = array();
    var $unsolvedCallbacks = array();
    var $templateKeys = array('navigation', 'navigationsFor', 'cms', 'configuration', 'translate', 'title', 'loadPlugin', 'todo', 'ignore', 'help', 'pathTo');
    var $classes = array();
    var $Bambus = NULL;
    //init
    function __construct()
    {
        parent::Bambus();
    }
    
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
	function requestBambusCommand() //TODO: whats that doing?
	{
		return true;
	}


	//////////////////////
	//callback functions//
	//////////////////////
	
	function titleCallBack($string = '')
	{
		if($string != '')
		{
			//return $this->Navigations->getTitle($string);
		}
		else
		{
			//return implode(', ', $this->Navigations->getTitles());
		}
		return '';
	}
	
	function translateCallBack($string)
	{
		return SLocalization::get($string);
	}
	
	function pathToCallBack($path)
	{
		return (isset($this->paths[$path])) ? $this->paths[$path] : '';
	}
	
	function navigationCallBack($string)
	{
		//return $this->Navigations->generateNavigation($string);
	}
	
	function navigationsForCallBack($string)
	{
		//return $this->Navigations->generateNavigationsFor($string);
	}
	function configurationCallBack($string)
	{
		
		return utf8_encode($this->Configuration->get($string));
	}
	
	function cmsCallBack($string)
	{
		switch(strtolower($string))
		{
			case 'version': 		return BAMBUS_VERSION;
			case 'rootdir': 		return BAMBUS_CMS_ROOT;
			case 'diskspace':
			case 'freediskspace':
			case 'diskfreespace':	return $this->formatSize(disk_free_space(BAMBUS_CMS_ROOT));
			case 'memoryusage':
			case 'memusage':		return $this->formatSize(memory_get_usage(true));
			case 'starttime':
			case 'execstart':		return BAMBUS_EXEC_START;
			case 'gentime':
			case 'runtime':			return round((microtime(true) - BAMBUS_EXEC_START),2);
			case 'bambuslogo':		return 'System/Images/BambusCMSLogo.png';
			case 'applicationlogo': return BAMBUS_APPLICATION_ICON;
			case 'applicationtitle': return BAMBUS_APPLICATION_TITLE;
			case 'applicationtablogo':return BAMBUS_APPLICATION_TAB_ICON;
			case 'applicationtabtitle':return BAMBUS_APPLICATION_TAB_TITLE;
			case 'currentobject':	return BAMBUS_CURRENT_OBJECT;
			case 'currentobjecttitle':return substr(BAMBUS_CURRENT_OBJECT,0,strlen($this->suffix(BAMBUS_CURRENT_OBJECT))*-1-1);
			case 'currentobjecticon':
				return WIcon::pathFor($this->suffix(BAMBUS_CURRENT_OBJECT),'mimetype');
			default: 				return '';
		}
	}
	
	
	//////////////////////////////////////////////
	//print function wrapper and template parser//
	//////////////////////////////////////////////
	
	function parse($string)
	{
		
		if(empty($string) || strlen($string) < 3) //empty or less than 3 is not worth parsing 
			return $string;
		//POSSIBLE END -> not worth parsing

		//find out what to parse
		$parseCallBacks = (strpos($string, '{') !== false && strpos($string, '}') !== false);
		$parseConditions = (strpos($string, '{?') !== false && $parseCallBacks);
		if(!$parseCallBacks && !$parseConditions)
			return $string;
		//POSSIBLE END -> nothing parseable
		
		$cmdStarts = array();
		$cmdEnds = array();
		$commands = array();
		$charOffset = 0;
		$lastErg = -1;
		$stringLength = strlen($string);
		
		//find all possible command start points 
		$loopo = 0;
		while(true)
		{
			$erg = strpos($string, '{', $charOffset);
			if($lastErg == $erg || $erg === false) break;
			$lastErg = $charOffset;
			$charOffset = $erg+1;
			$cmdStarts[] = $erg;
		}
		$charOffset = 0;
		$lastErg = -1;
		//find all possible end points
		while(true)
		{
			$erg = strpos($string, '}', $charOffset);
			if($lastErg == $erg || $erg === false) 
				break;
			$lastErg = $charOffset;
			$charOffset = $erg+1;
			$cmdEnds[] = $erg;
		}
		//match end points to their start
		$loops = min(count($cmdStarts), count($cmdEnds));
		for($i = 0; $i < $loops; $i++)
		{
			//current to match is $cmdEnds[i]
			$ended = false;
			$m = -1;//to start with sero
			$match = -1;
			while(true)
			{
				//walk through starting points
				//the start must be before the end
				$m++;
				if(!isset($cmdStarts[$m]) || $cmdStarts[$m] >= $cmdEnds[$i])
					break;
				$match = $cmdStarts[$m];
			}
			if($match != -1)
			{
				//something found
				//position of found element is in $m
				//element is in $match
				//remove element from list of free elements
				unset($cmdStarts[$m-1]);
				//and correct the index
				$cmdStarts = array_values($cmdStarts);
				$commands[] = array($match, $cmdEnds[$i]);
			}
		}
		if(count($commands) == 0)
			return $string;
		//POSSIBLE END -> no commands found (if it ends here something is wrong)
		//command execution..
		$replace = array();
		$SCI = SComponentIndex::alloc()->init();
		foreach($commands as $cmd)
		{
			$command = substr($string, $cmd[0]+1, $cmd[1]-$cmd[0]-1);
			if(strlen($command) > 0)
			{
				$firnstChar = substr($command, 0, 1);
				if($firnstChar >= 'A' && $firnstChar <= 'Z' && strpos($command, ':') !== false)
				{
					//Class call
					$parameters = array();

					//which class?
					$temp = explode(':',$command);
					$class = $temp[0];
					unset($temp[0]);
					$functionString = implode(':', $temp);

					//which function?
					$temp = explode('?',$functionString);
					$function = $temp[0];
					unset($temp[0]);
					//new class handling
					if(!isset($this->{$class}) && class_exists($class))
					{
						$tmp = new $class();
						$this->{$class} = $tmp->alloc();
						$this->{$class}->init();
						unset($tmp);
	   				}
					//end of new class handling
					if(
						isset($this->{$class})
						&&is_object($this->{$class})
						&&method_exists($this->{$class}, 'allowCallFromTemplate')
						&& $this->{$class}->allowCallFromTemplate($function)
					  )
					{
						//which parameters
						if(count($temp) > 0)
						{
							$parameterString = implode('?', $temp);
							if(!empty($parameterString) && strlen($parameterString) > 2) //a=b is minimum
							{
								$pairs = explode('&', $parameterString);
								$i = 0;
								while ($i < count($pairs)) 
								{
								    $pair = explode('=', $pairs[$i]);
								    $first = urldecode($pair[0]);
								    unset($pair[0]);
								    $rest = urldecode(implode('=', $pair));
								    $parameters[$first] = $rest;
								    $i++;
								}
							}
						}
						if(method_exists($this->{$class}, $function))
						{
							//call class function
							$replace[$command] = $this->{$class}->{$function}($parameters);
						}
						elseif(isset($this->{$class}->{$function}))
						{
							$replace[$command] = $this->{$class}->{$function};
						}
						else
						{
							//call class property
							$properties = array_keys(get_object_vars($this->{$class}));
							if(in_array($function, $properties))
							{
								$replace[$command] = $this->{$class}->{$function};
							}
						}
					}
					
				}
				elseif($firnstChar == "W" && ctype_alpha($command))
				{
					try{
						if(class_exists($command, true) && $SCI->IsExtension($command, 'BWidget'))
						{
							$replace[$command] = new $command(null);
							continue;
						}
					}catch(Exception $e){/*Ignore*/}
				}
				elseif($firnstChar >= 'a' && $firnstChar <= 'z')
				{
					if(strpos($command, ':') !== false)
					{
						//template callback {function:value}
						$temp = explode(':',$command);
						$callBackFunction = $temp[0].'CallBack';
						if(in_array($temp[0], $this->templateKeys))
						{
							unset($temp[0]);
							$callBackValue = implode(':', $temp);
							if(method_exists($this, $callBackFunction))
							{
								$replace[$command] = ($this->{$callBackFunction}($callBackValue));
							}
							else
							{
								$replace[$command] = $command;
							}
						}
					}
					else
					{
						//env var
						if($this->Configuration->exists($command))
						{
							$replace[$command] = utf8_encode($this->Configuration->get($command));
						}
						else
						{
							$replace[$command] = $command;
						}
					}
				}
				elseif($firnstChar == '?')
				{
					//TODO: implement template conditions
				}
			}
		}
		foreach($replace as $function => $result)
		{
			$result = mb_convert_encoding($result, "UTF-8", "auto");
			$string = str_replace('{{'.$function.'}}', htmlentities($result, ENT_QUOTES, 'UTF-8'), $string);
			$string = str_replace('{'.$function.'}', $result, $string);
		}

		return $string;
	}
	
	public function parse__Object($Object, $template, $templateKind = 'String')
	{
		//{function?parname1=parval1&parname2=parval2...}
		//{property}
		
		
	
	
	
	
	}
	
	
	//print with enviornment, navigation, configuration veriables and php function calls 
	function bprint($string)
	{
		echo $this->bsprint($string);
	}
	
	//as string //base of all format functions in this class
	function bsprint($string)
	{
		return $this->parse($string);
	}
	
	//plus user defined vars
	function bprintv($string, $arrayOfVars)
	{
		echo $this->bsprintv($string, $arrayOfVars);
	}
	
	//as string
	function bsprintv($string, $arrayOfVars)
	{
		if(is_array($arrayOfVars))
		{
			foreach($arrayOfVars as $key => $value)
			{
				$value = mb_convert_encoding($value, "UTF-8", "auto");
				while(strpos($string, '{{'.$key.'}}') !== false)
				{
					$string = str_replace('{{'.$key.'}}', htmlentities($value, ENT_QUOTES, 'UTF-8'), $string);
				}
				while(strpos($string, '{'.$key.'}') !== false)
				{
					$string = str_replace('{'.$key.'}', $value, $string);
				}
			}
		}
		return $this->bsprint($string);
	}
	
	//bprint with printf formaters
	function bprintf($string)
	{
		$numOfArgs = func_num_args();
		if($numOfArgs > 1)
		{
			$args = func_get_args();
			array_shift($args);
			$string = vsprintf($string, $args);
		}
		echo $this->bsprint($string);
	}
	
	//as string
	function bsprintf($string)
	{
		$numOfArgs = func_num_args();
		if($numOfArgs > 1)
		{
			$args = func_get_args();
			array_shift($args);
			$string = vsprintf($string, $args);
		}
		return $this->bsprint($string);
	}
	
	//bprintv with printf formaters
	function bprintvf($string, $arrayOfVars)
	{
		$numOfArgs = func_num_args();
		if($numOfArgs > 2)
		{
			$args = func_get_args();
			array_shift($args);
			array_shift($args);
			$string = vsprintf($string, $args);
		}
		echo $this->bsprintv($string, $arrayOfVars);
	}
	
	//as string		
	function bsprintvf($string, $arrayOfVars)
	{
		$numOfArgs = func_num_args();
		if($numOfArgs > 2)
		{
			$args = func_get_args();
			array_shift($args);
			array_shift($args);
			$string = vsprintf($string, $args);
		}
		return $this->bsprintv($string, $arrayOfVars);	
	}
}
?>