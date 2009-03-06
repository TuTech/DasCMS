<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-20
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
 */
interface IGeneratesFeed
{
    public function startFeedReading();
    public function hasMoreFeedItems();
	public function getFeedItemData();
	public function getFeedMetaData();
	public function finishFeedReading();
}
?>