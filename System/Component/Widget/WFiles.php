<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 11.04.2008
 * @license GNU General Public License 3
 */
class WFiles extends BWidget implements ISidebarWidget 
{
	/**
	 * get category of this widget
	 * @return string
	 */
	public function getCategory()
	{
		return 'Media';
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Files';
	}
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public function supportsObject($object)
	{
		return $object !== null;
	}
	
	public function __construct($target)
	{
	}
	
	public function __toString()
	{
		//js insertMedia('image', $img, $title
	    //@todo remove chimera hack
	    global $Bambus;	
		$files = DFileSystem::FilesOf('Content/download/');
		$html = "";
		foreach($files as $file)
		{
			$suffix = substr($file, strrpos($file,'.')+1);
			$image = (file_exists('System/Icons/48x48/mimetypes/'.$suffix.'.png')) ? $suffix : 'file';
			$html .= sprintf(
				"<div class=\"thumbnail no-border\" onclick=\"insertMedia('file','Content/download/%s','%s')\">".	
					"<img src=\"System/Icons/48x48/mimetypes/%s.png\" alt=\"%s\" />%s</div>\n"
				,htmlentities($file)
				,htmlentities($file)
				,htmlentities($image)
				,htmlentities($file)
				,str_replace(chr(11), ' ', wordwrap(htmlentities(str_replace(' ', chr(11), $file)),12,"<wbr />",true))
			);
		}
		return $html;
	}
}
?>