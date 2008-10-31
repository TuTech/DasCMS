<?php
/**
 * @package Bambus
 * @subpackage Events
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-31
 * @license GNU General Public License 3
 */
class EWillAccessContentEvent extends BEvent
{
	public function __construct(BObject $sender, BContent $content)
	{
		$this->Sender = $sender;
		$this->Content = $content;
		parent::informHandlers($this);
	}
	
	public function substitute(BContent $content)
	{
	    $this->Content = $content;
	    $this->contentSubstituted = true;
	}
	
	public function hasContentBeenSubstituted()
	{
	    return $this->contentSubstituted;
	}
	
	 /**
	 * @var BContent
	 */
	protected $Content;
	private $contentSubstituted = false;
}
?>