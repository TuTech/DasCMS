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
	
	private $attribute, $type, $contexts = array(), $entries = array();
	private $readOnly = false;
	
	/**
	 * @param string $attribute
	 * @param string $type
	 * @param array $contexts
	 * @param array $entries
	 * @throws XArgumentException
	 */
	public function __construct($attribute, $type, array $contexts)
	{		
	    if(!in_array($type, WCPersonAttributes::getTypes()))
	    {
	        throw new XArgumentException('invalid type');
	    }
	    $this->attribute = $attribute;
	    $this->type = $type;
	    $this->contexts = $contexts;
	}
	
	/**
	 * @param string $context
	 * @return boolean
	 */
	public function hasContext($context)
	{
	    return in_array($context, $this->contexts);
	}
	
	public function getName()
	{
	    return $this->attribute;
	}
	
	public function getType()
	{
	    return $this->type;
	}
	
	/**
	 * @return array
	 */
	public function getContexts()
	{
	    return $this->contexts;
	}
	
	/**
	 * @return array
	 */
	public function getEntries()
	{
	    return $this->entries;
	}
	
	public function getContextID($context)
	{
	    return array_search($context, $this->contexts);
	}
	
	/**
	 * set read only
	 * @return WCPersonAttribute
	 */
	public function asContent()
	{
	    $this->readOnly = true;
	    foreach ($this->entries as $ent)
	    {
	        $ent->asContent();
	    }
	    return $this;
	}
	
	public function asArray()
	{
	    $ents = array();
	    foreach ($this->entries as $ent)
	    {
	        $ents[] = $ent->asArray();
	    }
	    return array(
	        'contexts' => $this->contexts,
	        'entries' => $ents,
            'type' => WCPersonAttributes::getTypeID($this->type)
	    );
	}
	
	/**
	 * add new entry
	 * @param WCPersonEntry $entry
	 * @return void
	 */
	public function addEntry($entry)
	{
	    if($this->readOnly || !$entry instanceof WCPersonEntry)
	    {
	        return;
	    }
	    $this->entries[] = $entry;
	}
	
	/**
	 * get render() output as string
	 *
	 * @return string
	 */
	public function __toString()
	{
	    $out = '<h3>'.SLocalization::get($this->attribute)."</h3>\n".
	    		'<dl class="CPersonAttribute CPersonAttribute_'.md5($this->attribute).'">'."\n";
	    foreach ($this->entries as $ent)
	    {
	        $out .= strval($ent);
	    }
	    $out .= "</dl>\n";
	    return $out;
	}
	
	private function encode($string)
	{
	    return htmlentities(mb_convert_encoding($string, 'UTF-8', 'UTF-8,ISO-8859-1'), ENT_QUOTES, 'UTF-8');
	}
}
?>