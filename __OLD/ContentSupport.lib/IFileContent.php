<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-05
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
 */
interface IFileContent
{
    const ENCLOSURE_URL = '%sfile.php/get/%s';
    public function getFileName();// style.css
    public function getType();// css
	public function getDownloadMetaData();//[filename, type, size]
	public function sendFileContent();
	public function getRawDataPath();
}
?>