<?php
/**
 * Atom generator element
 */
class XML_Atom_Generator extends _XML_Atom 
{
    protected $uri = null;
    protected $version = null;    
    protected $text = '';    
    
    protected static $_attributes = array(
        'uri' => _XML::NONE_OR_MORE,
        'version' => _XML::NONE_OR_MORE
    );
    
    protected function getElementParsers()
    {
        return array();
    }
    
    protected function __construct()
    {
    }
    
    /**
     * create a XML_Atom_Feed by feed-node
     *
     * @param DOMNode $node
     * @return XML_Atom_Generator
     */
    public static function fromNode(DOMNode $node)
    {
        $generator = new XML_Atom_Generator();
        $generator->parseNodeAttributes($node, self::$_attributes);
        $generator->text = $node->textContent;
        return $generator;
    }
}
?>