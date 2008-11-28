<?php
/**
 * Atom feed element
 */
class XML_Atom_Text extends _XML_Atom 
{
    protected $data = '';
    protected $type = 'text';    
    
    
    protected static $_elements = array(
    );
     
    private static $_elementParser = array(
    );
     
    protected function getElementParsers()
    {
        return self::$_elementParser;
    }
    
    protected static $_attributes = array('type' => _XML::NONE_OR_MORE);
    
    protected function __construct()
    {
    }
    
    /**
     * create a XML_Atom_Feed by feed-node
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
    
    public function __toString()
    {
        return strval($this->data);
    }
}
?>