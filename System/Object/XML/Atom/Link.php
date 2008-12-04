<?php
/**
 * Atom link element
 */
class XML_Atom_Link extends _XML_Atom implements Interface_XML_Atom_ToDOMXML
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
    
    /**
     * @param string $href
     * @param string $rel optional
     * @param string $type optional
     * @param string $hreflang optional
     * @param string $title optional
     * @param string $length optional
     * @return XML_Atom_Link
     */
    public static function create($href, $rel = null, $type = null, $hreflang = null, $title = null, $length = null)
    {
        $o = new XML_Atom_Link();
        foreach (self::$_attributes as $att => $mode) 
        {
        	if(${$att})$o->{$att} = ${$att};
        }
        return $o;
    }
    
    protected function __construct()
    {
    }
    
    protected function getElementDefinition()
    {
        return array();
    }
    
    protected function getAttributeDefinition()
    {
        return self::$_attributes;
    }
    
    public static function createSelfLink()
    {
        $link = new XML_Atom_Link();
        $link->rel = 'self';
        $link->href = SLink::selfURI();
        return $link;
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
    
    public function getHRef()
    {
        return $this->href;
    }     
    
    public function getRel()
    {
        return $this->rel;
    }     
    
    public function getType()
    {
        return $this->type;
    }     
    
    public function getHRefLang()
    {
        return $this->hreflang;
    }     
    
    public function getTitle()
    {
        return $this->title;
    }     
    
    public function getLength()
    {
        return $this->length;
    }     
}
?>