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
	 * @var WCPersonAttribute
	 */
	private $parent;
	private $context, $value;
	private $readOnly = false;
	
	/**
	 * @param WCPersonAttribute $parentAttribute
	 * @param string $context
	 * @param string $value
	 * @throws XArgumentException
	 */
	public function __construct(WCPersonAttribute $parentAttribute, $context, $value)
	{		
	    if(!$parentAttribute->hasContext($context))
	    {
	        throw new XArgumentException('invalid context');
	    }
	    $value = WCPersonAttributes::validatedValue($value, $parentAttribute->getType());
	    if($value === null)
	    {
	        throw new XArgumentException('invalid value');
	    }
	    $this->parent = $parentAttribute;
	    $this->context = $context;
	    $this->value = $value;
	}
	
	/**
	 * make read only
	 * @return WCPersonEntry
	 */
	public function asContent()
	{
	    $this->readOnly = true;
	    return $this;
	}
	
	public function asArray()
	{
	    return array(
	        $this->parent->getContextID($this->context),
	        $this->value
        );
	}
	
	public function getContext()
	{
	    return $this->context;
	}
	
	public function getValue()
	{
	    return $this->value;
	}
	
	/**
	 * get render() output as string
	 *
	 * @return string
	 */
	public function __toString()
	{
	    return '<dt>'.$this->encode($this->context)."</dt>\n<dd>".$this->encode($this->value)."</dd>\n";
	}
	
	private function encode($string)
	{
	    return htmlentities(mb_convert_encoding($string, CHARSET, 'UTF-8,ISO-8859-1'), ENT_QUOTES, CHARSET);
	}
}
?>