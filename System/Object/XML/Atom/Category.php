<?php
/**
 * Atom category element
 */
class XML_Atom_Category extends _XML_Atom 
{
    protected $term;
    protected $scheme = null;    
    protected $label = null;    
    
    protected static $_attributes = array(
        'term' => _XML::EXACTLY_ONE,
        'scheme' => _XML::NONE_OR_MORE,
        'label' => _XML::NONE_OR_MORE
    );
    
    protected function __construct()
    {
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