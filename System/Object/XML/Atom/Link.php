<?php
/**
 * Atom link element
 */
class XML_Atom_Link extends _XML_Atom 
{
    protected $href;
    protected $rel = null;    
    protected $type = null;    
    protected $hreflang = null;    
    protected $title = null;    
    protected $length = null;    
        
    protected static $_attributes = array(
        'href' 		=> _XML::EXACTLY_ONE,
        'rel' 		=> _XML::NONE_OR_MORE,
        'type' 		=> _XML::NONE_OR_MORE,
        'hreflang' 	=> _XML::NONE_OR_MORE,
        'title' 	=> _XML::NONE_OR_MORE,
        'length' 	=> _XML::NONE_OR_MORE
    );
    
    protected function __construct()
    {
    }
    
    /**
     * create a XML_Atom_Link by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Link
     */
    public static function fromNode(DOMNode $node)
    {
        $link = new XML_Atom_Link();
        $link->parseNodeAttributes($node, self::$_attributes);
        return $link;
    }
}
?>