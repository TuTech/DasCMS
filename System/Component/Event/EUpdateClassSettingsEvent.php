<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Events
 */
class EUpdateClassSettingsEvent extends BEvent
{
    protected $data = array();
	public function __construct(AConfiguration $sender, array $data)
	{
		$this->Sender = $sender;
		$this->data = $data;
		parent::informHandlers($this);
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