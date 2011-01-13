<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2011-01-13
 * @license GNU General Public License 3
 */
abstract class _EventContent extends _Event
{
	protected $Content = null;

	/**
	 * @return Interface_Content
	 */
	public function getContent(){
		return $this->Content;
	}
}
?>