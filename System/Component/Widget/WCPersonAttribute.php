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
class WCPersonAttribute extends BWidget 
{
	const CLASS_NAME = "WCPersonAttribute";
	
	private $attribute;
	private $readOnly = false;
	private $entries = array();
	
	public function __construct(WCPersonAttributes $parent, $attribute, $type, $contexts, array $entries = array())
	{		
	    $this->attribute = $attribute;
	    foreach ($entries as $ent)
	    {
	        $this->addEntry($ent);
	    }
	}
	
	public function asContent()
	{
	    $this->readOnly = true;
	    return $this;
	}
	
	public function addEntry(WCPersonEntry $entries)
	{
	    if($this->readOnly)
	    {
	        return;
	    }
	    $this->entries[] = $entries;
	}
	
	/**
	 * get render() output as string
	 *
	 * @return string
	 */
	public function __toString()
	{
	    $out = '<h3>'.$this->attribute.'</h3><div class="CPersonAttribute">';
	    foreach ($this->entries as $ent)
	    {
	        $out .= strval($ent);
	    }
	    $out .= '</div>';
	    return $out;
	}
	
	private function encode($string)
	{
	    return htmlentities(mb_convert_encoding($string, 'UTF-8', 'UTF-8,ISO-8859-1'), ENT_QUOTES, 'UTF-8');
	}
}
?>