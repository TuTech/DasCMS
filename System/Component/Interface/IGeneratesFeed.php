<?php
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 20.10.2008
 * @license GNU General Public License 3
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