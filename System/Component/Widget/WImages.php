<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 11.04.2008
 * @license GNU General Public License 3
 */
class WImages extends BWidget implements ISidebarWidget 
{
	private $targetClass = null;
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
		return 'Images';
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
		if($target !== null && is_object($target))
		{
			$this->targetClass = get_class($target);
		}
	}
	
	public function __toString()
	{
		//js insertMedia('image', $img, $title
	    //@todo remove chimera hack
	    global $Bambus;	
	    if($this->targetClass !== null)
	    {
	    	//content stuff wants content images
	    	$images = DFileSystem::FilesOf('Content/images/', "/\\.(jpg|png|gif|jpeg)$/");
	    	$path = 'Content/images/';
	    }
	    else
	    {
	    	//no content - might be interested in design images
	    	$images = DFileSystem::FilesOf('Content/stylesheets/', "/\\.(jpg|png|gif|jpeg)$/");
	    	$path = '';
	    }
		$html = "";
		foreach($images as $file)
		{
			$imagePath = html_entity_decode(SLink::link(array('render' => $file, 'path' => ($this->targetClass !== null) ? 'image':'design'),'thumbnail.php'));
			
			$html .= sprintf(
				"<div class=\"thumbnail\" onclick=\"insertMedia('image','%s%s','%s')\">".	
					"<img src=\"%s\" alt=\"%s\" />%s</div>\n"
				,htmlentities($path)
				,htmlentities($file)
				,htmlentities(substr($file,0,strrpos($file, '.')))
				,htmlentities($imagePath)
				,htmlentities($file)
				,str_replace(chr(11), ' ', wordwrap(htmlentities(str_replace(' ', chr(11), $file)),12,"<wbr />",true))
			);
		}
		return $html;
	}
}
?>