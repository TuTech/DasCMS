<?php
/**
 * Atom person element
 */
class XML_Atom_Person extends _XML_Atom implements Interface_XML_Atom_ToDOMXML
{
    protected $c__name;
    protected $c__email = null;
    protected $c__uri = null;
    
    protected static $_elements = array(
        'name' 	=> _XML::EXACTLY_ONE,
        'email'	=> _XML::NONE_OR_MORE,
        'uri'	=> _XML::NONE_OR_MORE
    );
    
    private static $_elementParser = array(
        'name' 	=> 'XML_Atom_Text',
        'email'	=> 'XML_Atom_Text',
        'uri'	=> 'XML_Atom_Text'
    );
     
    protected function getElementParsers()
    {
        return self::$_elementParser;
    }
    
    protected function getElementDefinition()
    {
        return self::$_elements;
    }
    
    protected function getAttributeDefinition()
    {
        return array();
    }
    
    protected function __construct()
    {
    }
    
    /**
     * create a XML_Atom_Person by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Person
     */
    public static function fromNode(DOMNode $node)
    {
        $person = new XML_Atom_Person();
        $person->parseNodeElements($node, self::$_elements);
        return $person;
    }
    
    public function getName()
    {
        return $this->getFirstChild('name');
    }     
    
    public function getEMail()
    {
        return $this->getFirstChild('email');
    }     
    
    public function getURI()
    {
        return $this->getFirstChild('uri');
    }     
    
    //FIXME to _XML

}
?>