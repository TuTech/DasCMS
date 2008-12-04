<?php
/**
 * Atom generator element
 */
class XML_Atom_Generator extends _XML_Atom implements Interface_XML_Atom_ToDOMXML
{
    protected $uri = null;
    protected $version = null;    
    protected $text = '';    
    
    protected static $_attributes = array(
        'uri' 	  => _XML::NONE_OR_MORE,
        'version' => _XML::NONE_OR_MORE
    );
    
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
    
    protected function isDataNode()
    {
        return true;
    }
    
    protected function getNodeData()
    {
        return $this->text;
    }
    
    /**
     * create a XML_Atom_Generator by node
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
    
    public function getURI()
    {
        return $this->uri;
    }     
    
    public function getVersion()
    {
        return $this->version;
    }     
    
    public function getText()
    {
        return $this->text;
    }     
}
?>