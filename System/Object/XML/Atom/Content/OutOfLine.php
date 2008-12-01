<?php
/**
 * external content
 */
class XML_Atom_Content_OutOfLine extends _XML_Atom_Content
{
    protected $src = null; 
    protected static $_attributes = array(
        'type' => _XML::NONE_OR_MORE,
        'src' => _XML::NONE_OR_MORE
    );
    /**
     * create a XML_Atom_Content_Other by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Content_Other
     */
    public static function fromNode(DOMNode $node)
    {
        $content = new XML_Atom_Content_OutOfLine();
        $content->parseNodeAttributes($node, self::$_attributes);
        return $content;
    }
    
    public function getSource()
    {
        return $this->src;
    } 
    
    public function __toString()
    {
        return '';
    }
}
?>