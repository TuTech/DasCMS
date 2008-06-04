<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 23.08.2007
 * @license GNU General Public License 3
 */
class Event
{
	public $Sender = NULL;
	private $Type = '';
	private $Data = array();
	
	private $HandledBy = array();
	private $Handled;
	
	public function __construct($type, $sender, $data = array())
	{
		$this->Type = $type;
		$this->Sender = $sender;
		$this->Data = $data;
		
		//all systems go
		//get_declared_interfaces() - interface for me?
		//get_declared_classes()    - any listeners to my interface? 
		$eventInterface = 'Event'.$type;
		if(in_array($eventInterface, get_declared_interfaces()))
		{
			$slave = NULL;
			$receiver = NULL;
			$possibleListeners = get_declared_classes();
			foreach($possibleListeners as $pl)
			{
				$ci = class_implements($pl, false);
				if(in_array($eventInterface, $ci) && in_array('IShareable', $ci))
				{
					//get the shared instance
					$slave = new $pl();
					$receiver = $slave->alloc();
					$receiver->init();
					//call receivers handleEventEventName(this event)
					$receiver->{'handleEvent'.$type}($this);
				}
			}
		}
	}
	
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
	public function __get($Prop)
	{
		switch($Prop)
		{
			case 'Sender':
				return $this->Sender;
			case 'Type':
				return $this->Type;
			case 'Handled':
				return (count($this->HandledBy) > 0);
			case 'HandledBy':
				return $this->HandledBy;
			default:
				return isset($this->Data[$Prop]) ?  $this->Data[$Prop] : NULL;
		}
	}
	
	public function __set($var, $value)
	{
		//event->HandledBy = HandlingClassName
		if($var == 'HandledBy' && in_array($value, get_declared_classes()))
		{
			$this->HandledBy[$value] = $value;
			return true;
		}
		return false;
	}
	
	public function __isset($Prop)
	{
		switch($Prop)
		{
			case 'Sender':
			case 'Type':
			case 'Handled':
			case 'HandledBy':
				return true;
			default:
				return isset($this->Data[$Prop]) ?  $this->Data[$Prop] : false;
		}
	
	}
}
?>