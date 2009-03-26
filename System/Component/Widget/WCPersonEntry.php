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
class WCPersonEntry extends BWidget 
{
	const CLASS_NAME = "WCPersonEntry";
	
	/**
	 * @var CPerson
	 */
	private $person;
	private $attribute;
	private $readOnly = false;
	private $entries = array();
	
	public function __construct(WCPersonAttribute $parent, $context, $value)
	{		
	    $this->attribute = $attribute;
	}
	
	public function asContent()
	{
	    $this->readOnly = true;
	    return $this;
	}
	
	
	/**
	 * get render() output as string
	 *
	 * @return string
	 */
	public function __toString()
	{
	    return '<h4>'.$this->attribute.'</h4>';
	}
	
	private function encode($string)
	{
	    return htmlentities(mb_convert_encoding($string, 'UTF-8', 'UTF-8,ISO-8859-1'), ENT_QUOTES, 'UTF-8');
	}
}
?>