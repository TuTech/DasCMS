<?php
/**
 * @package Bambus
 * @subpackage CommandQueries 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 17.12.2007
 * @license GNU General Public License 3
 */
class QCommand extends BObject 
{
	public $user = 'guest';
	public $password = 'guest';
	public $controller = '';
	public $method = '';
	public $parameters = array();
	public $requestId = 0;

	/**
	 * Create a new command
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $controller
	 * @param string $method
	 * @param array $parameters
	 * @return BCMSCommand
	 */
	public static function Create($username, $password, $controller, $method, array $parameters)
	{
		$obj = new QCommand();
		$obj->username = $username;
		$obj->password = $password;
		$obj->controller = $controller;
		$obj->method = $method;
		$obj->parameters = $parameters;
		
		return $obj;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $jsonstring
	 * @return BCMSCommand
	 */
	public static function FromJSON($jsonstring)
	{
		$arr = json_decode($jsonstring, true);
		if(!is_array($arr))
		{
			throw new XInvalidDataException('invalid json string');
		}

		$cmd = new QCommand();
		foreach($arr as $key => $value)
		{
			if(isset($cmd->{$key}))
				$cmd->{$key} = $value;
		}
		return $cmd;
	}
	
	/**
	 * Command as json string
	 *
	 * @return string
	 */
	public function AsJSON()
	{
		return json_encode($this);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $xml
	 * @return BCMSCommand
	 */
	public static function FromWDDX($xml)
	{
		$obj = wddx_deserialize($xml);
		if ($obj instanceof QCommand) 
		{
			return $obj;
		}
		else
		{
			throw new XInvalidDataException('invalid wddx string');
		}
	}
	
	/**
	 * Command as wddx xml string
	 *
	 * @return string
	 */
	public function AsWDDX()
	{
		return wddx_serialize_value($this);
	}
	
	/**
	 * execute command
	 *
	 * @return QResult
	 */
	public function Execute()
	{
		$res = null;
		//do some calls
//		echo "sci init\n";
		$SCI = SComponentIndex::alloc()->init();
//		echo "check app ctrl '{$this->controller}'\n";
		try 
		{
			if(!empty($this->controller) && $SCI->IsExtension($this->controller, 'BAppController'))
			{
	//			echo "app ctrl init\n";
				$CTRL = new $this->controller();
				
	//			echo "check method '{$this->method}'\n";
				if(method_exists($CTRL,$this->method))
				{
	//				echo "call method\n";
					$res = call_user_func_array(array($CTRL,$this->method), $this->parameters);
	//				echo $res;
				}
			}
			elseif(empty($this->controller))
			{
				//internal request
				switch ($this->method) 
				{	
					case 'ping':
						$res = array('pong' => time());	
						break;
					case 'Controllers':
						$res = $SCI->ExtensionsOf('BAppController');
						break;
					case 'Methods':
					case 'MethodsOf':
						if(!empty($this->parameters[0]) 
							&& $SCI->IsExtension($this->parameters[0], 'BAppController'))
						{
							$res = get_class_methods($this->parameters[0]);
						}
				}
			}
		}
		catch (Exception $e)
		{
			$res = $e;
		}
		return new QResult($this->requestId, $this->controller, $this->method, $res);
	}
}
?>