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

	public static function getTypes()
	{
	    return array('text', 'email', 'phone', 'textbox');
	}
	
	public static function getTrimmedTypes()
	{
	    return array('email' => 1, 'phone' => 1);
	}
	
	public static function getTypeValidators()
	{
	    return array(
	    	'email' => "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)".
						"*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?");
	}
	
	public static function getTypeReplacers()
	{
	    return array('phone' => array('([^0-9\-\/\*\.\+]|[\s])+', ' '));
	}
	
	public static function getTypeID($type)
	{
	    return array_search($type,self::getTypes());
	}
	
	public static function validatedValue($val, $forType)
	{
	    return $val;
	}
	
	public function __construct(array $attributes = array())
	{		
	    foreach ($attributes as $att)
	    {
	        $this->addAttribute($att);
	    }
	}
	
	/**
	 * set read only
	 * @return WCPersonAttributes
	 */
	public function asContent()
	{
	    $this->readOnly = true;
	    foreach ($this->attributes as $att)
	    {
	        $att->asContent();
	    }
	    return $this;
	}
	
	public function asArray()
	{
	    $atts = array();
	    foreach($this->attributes as $att)
	    {
	        $atts[$att->getName()] = $att->asArray();
	    }
	    return array(
	        'attributes' => $atts,
            'types' => self::getTypes(),
            'trim' => self::getTrimmedTypes(),
            'replace' => self::getTypeReplacers(),
            'check' => self::getTypeValidators()
	    );
	}
	
	/**
	 * @param WCPersonAttribute $attribute
	 * @return void
	 */
	public function addAttribute($attribute)
	{
	    if($this->readOnly || !$attribute instanceof WCPersonAttribute)
	    {
	        return;
	    }
	    $this->attributes[$attribute->getName()] = $attribute;
	}	
	
	/**
	 * @param string $attributeName
	 * @return WCPersonAttribute
	 */
	public function getAttribute($attributeName)
	{
	    if(!isset($this->attributes[$attributeName]))
	    {
	        throw new XUndefinedIndexException('no such attribute');
	    }
	    return $this->attributes[$attributeName];
	}
	
	
	public function getAttributes()
	{
	    return $this->attributes;
	}
		
	public function hasAttribute($attributeName)
	{
	    return isset($this->attributes[$attributeName]);
	}
	
	
	
	/**
	 * get render() output as string
	 *
	 * @return string
	 */
	public function __toString()
	{
	    $out = '<div class="CPerson">'."\n";
	    foreach($this->attributes as $att)
	    {
	        $out .= strval($att);
	    }
	    $out .= "</div>\n";
	    return $out;
	}
}
?>