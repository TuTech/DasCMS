<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Events
 */
class EWillAccessContentEvent extends BEvent
{
	public function __construct(BObject $sender, Interface_Content $content)
	{
		$this->Sender = $sender;
		$this->Content = $content;
		parent::informHandlers($this);
	}
	
	public function substitute(Interface_Content $content)
	{
	    $this->Content = $content;
	    $this->contentSubstituted = true;
	}
	
	public function hasContentBeenSubstituted()
	{
	    return $this->contentSubstituted;
	}
	
	 /**
	 * @var Interface_Content
	 */
	protected $Content;
	private $contentSubstituted = false;
}
?>