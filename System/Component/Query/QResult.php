<?php
/**
 * @package Bambus
 * @subpackage CommandQueries 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 17.12.2007
 * @license GNU General Public License 3
 */
class QResult extends BObject 
{
	public $requestId = 0;
	public $controller;
	public $method;
	public $data;
	
	public function __get($var)
	{
		switch (strtoupper($var))
		{
			case 'JSON':
				return json_encode($this);
			case 'WDDX':
				return wddx_serialize_value($this);
		}
	}
	
	public function __set($var, $value)
	{}
	
	public function __construct($requestId, $controller, $method, $data)
	{
		$this->requestId = $requestId;
		$this->controller = $controller;
		$this->method = $method;
		$this->data = $data;
	}
}

?>