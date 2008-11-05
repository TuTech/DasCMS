<?php
interface IFileContent
{
    const ENCLOSURE_URL = '%sfile.php?get=%s';
    public function getFileName();// style.css
    public function getType();// css
    public function getExtraSmallIcon();// 16x16 image url
    public function getSmallIcon();// 22x22 image url
    public function getMediumIcon();// 32x32 image url
    public function getLargeIcon();// 48x48 image url
	public function getDownloadMetaData();//[filename, type, size]
	public function sendFileContent();
}
?>