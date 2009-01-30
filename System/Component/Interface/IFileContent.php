<?php
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