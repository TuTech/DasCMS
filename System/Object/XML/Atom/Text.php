<?php
/**
 * Atom text element
 */
class XML_Atom_Text extends _XML_Atom implements Interface_XML_Atom_ToDOMXML
{
    /**
     * the text
     *  
     * @var string
     */
    protected $data = '';
    /**
     * the text type 
     * 
     * @var string
     */
    protected $type = 'text';    
    
    protected static $_attributes = array('type' => _XML::NONE_OR_MORE);
    
    /**
     * @return XML_Atom_Text
     */
    public static function create($text, $type = null)
    {
        $o = new XML_Atom_Text();
        if($type)$o->type = $type;
        $o->data = $text;
        return $o;
    }
    
    protected function __construct()
    {
    }
    
    protected function ignoreAttribute($nodeName, $attributeName, $value)
    {
        return (
            $nodeName == 'id' || 
            ($attributeName == 'type' && $value == 'text'));
    }
    
    protected function getElementDefinition()
    {
        return array();
    }
    
    protected function getAttributeDefinition()
    {
        return self::$_attributes;
    }
    
    protected function isDataNode()
    {
        return true;
    }
    
    protected function getNodeData()
    {
        return $this->data;
    }
    
    /**
     * create a XML_Atom_Feed by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Text
     */
    public static function fromNode(DOMNode $node)
    {
        $text = new XML_Atom_Text();
        $text->parseNodeAttributes($node, self::$_attributes);
        if($text->type == 'xhtml')
        {
            foreach ($node->childNodes as $child) 
            {
            	if($child->localName == 'div')
            	{
            	    $text->data = strval($child->nodeValue);
            	}
            }
        }
        else
        {
            $text->data = strval($node->nodeValue);
        }
        return $text;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getText()
    {
        return $this->data;
    }    
    
    public function __toString()
    {
        return $this->data;
    }
}
?>