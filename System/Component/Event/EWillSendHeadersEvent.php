<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-04
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Events
 */
class EWillSendHeadersEvent extends BEvent
{
    //html > head > script|meta|link|object|style
    
	public function __construct(IHeaderAPI $sender)
	{
		$this->Sender = $sender;
		parent::informHandlers($this);
	}
	
	/**
	 * @return IHeaderAPI
	 */
	public function getHeader()
	{
	    return $this->Sender;
	}
}
?>