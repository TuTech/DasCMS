<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-05-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WContentTitle extends BWidget 
{
	const CLASS_NAME = "WContentTitle";
	
	/**
	 * @var BContent
	 */
	private $content;
	
	public function __construct($content)
	{		
	    $this->content = $content;
	}

	/**
	 * get render() output as string
	 *
	 * @return string
	 */
	public function __toString()
	{
	    $out = '';
		if($this->content instanceof BContent)
        {
            $out = sprintf('<input type="text" title="%s" id="content_title" name="title" value="%s"/>'.
            		'<input type="text" title="%s" id="content_subtitle" name="subtitle" value="%s"/>'
        		, SLocalization::get('title')
            	, $this->encode($this->content->Title)
            	, SLocalization::get('subtitle')
            	, $this->encode($this->content->SubTitle)
            	);
        }
        return $out;
	}
	
	private function encode($string)
	{
	    return htmlentities(mb_convert_encoding($string, 'UTF-8', 'UTF-8,ISO-8859-1'), ENT_QUOTES, 'UTF-8');
	}

	public function render()
	{
	    echo $this->__toString();
	}
	
	public function run()
	{
	}
	/**
	 * return ID of primary editable element or null 
	 *
	 * @return string|null
	 */
	public function getPrimaryInputID()
	{
		return "title".$this->ID;
	}
}
?>