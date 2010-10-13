<?php
class Event_UpdateClassSettings extends _Event
{
    protected $data = array();
	public function __construct(AConfiguration $sender, array $data)
	{
		$this->Sender = $sender;
		$this->data = $data;
		$this->informHandlers();
	}
	
	/**
	 * get changed setting for the class of the given object
	 * @param BObject $object
	 * @return array
	 */
	public function getClassSettings(BObject $object)
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