<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-24
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WCPersonAttributes extends BWidget 
{
	const CLASS_NAME = "WCPersonAttributes";
	
	private $readOnly = false;
	private $attributes = array();

	public function __construct(array $attributes = array())
	{		
	    foreach ($attributes as $att)
	    {
	        $this->addAttribute($att);
	    }
	}
	
	public function asContent()
	{
	    $this->readOnly = true;
	    return $this;
	}
	
	public function addAttribute(WCPersonAttribute $attribute)
	{
	    if($this->readOnly)
	    {
	        return;
	    }
	    $this->attributes[] = $attribute;
	}	
	
	/**
	 * get render() output as string
	 *
	 * @return string
	 */
	public function __toString()
	{
	    $out = '<div class="CPerson">';
	    foreach($this->attributes as $att)
	    {
	        $out .= strval($att);
	    }
	    $out .='</div>';
	    return $out;
	}
	
	private function encode($string)
	{
	    return htmlentities(mb_convert_encoding($string, 'UTF-8', 'UTF-8,ISO-8859-1'), ENT_QUOTES, 'UTF-8');
	}
}
?>