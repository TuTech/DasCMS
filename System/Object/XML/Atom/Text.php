<?php
/**
 * Atom feed element
 */
class XML_Atom_Text extends _XML_Atom 
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
    
    protected function __construct()
    {
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
    
    public function __toString()
    {
        return $this->data;
    }
}
?>