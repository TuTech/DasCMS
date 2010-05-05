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
class ERequestingClassSettingsEvent extends BEvent
{
	/**
	 * @var AConfiguration
	 */
	protected $Sender = null;
	public function __construct(AConfiguration $sender)
	{
		$this->Sender = $sender;
		parent::informHandlers($this);
	}
	/**
	 * @param BObject $object owner of the config keys
	 * @param string $section section for these keys 
	 * @param array $settings key=>value
	 * @return void
	 */
	public function addClassSettings(BObject $object, $section, array $settings)
	{
	    $this->Sender->addSettings($section, get_class($object), $settings);
	}
}
?>