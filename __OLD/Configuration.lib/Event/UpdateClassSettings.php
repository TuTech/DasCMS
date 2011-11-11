<?php
class Event_UpdateClassSettings extends _Event
{
    protected $data = array();
	public function __construct(Controller_Application_Configuration $sender, array $data)
	{
		$this->sender = $sender;
		$this->data = $data;
		$this->informHandlers();
	}
	
	/**
	 * get changed setting for the class of the given object
	 * @param $object
	 * @return array
	 */
	public function getClassSettings($object)
	{
	    $key = md5(get_class($object));
	    if(isset($this->data[$key]))
	    {
	        return $this->data[$key];
	    }
	    else
	    {
	        return array();
	    }
	}
}
?>