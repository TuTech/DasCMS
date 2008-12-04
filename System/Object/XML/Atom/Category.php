<?php
/**
 * Atom category element
 */
class XML_Atom_Category extends _XML_Atom implements Interface_XML_Atom_ToDOMXML
{
    protected $term;
    protected $scheme = null;    
    protected $label = null;    
    
    protected static $_attributes = array(
        'term' => _XML::EXACTLY_ONE,
        'scheme' => _XML::NONE_OR_MORE,
        'label' => _XML::NONE_OR_MORE
    );
    
    /**
     * @return XML_Atom_Category
     */
    public static function create($term, $label = null, $scheme = null)
    {
        $o = new XML_Atom_Category();
        if($label)$o->label = $label;
        if($scheme)$o->scheme = $scheme;
        $o->term = $term;
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
    
    /**
     * create a XML_Atom_Category by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Category
     */
    public static function fromNode(DOMNode $node)
    {
        $category = new XML_Atom_Category();
        $category->parseNodeAttributes($node, self::$_attributes);
        if($category->label == null)
        {
            $category->label = $category->term;
        }
        return $category;
    }
    
    public function getTerm()
    {
        return $this->term;
    }     
    
    public function getScheme()
    {
        return $this->scheme;
    }     
    
    public function getLabel()
    {
        return $this->label;
    }     
}
?>