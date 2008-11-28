<?php
/**
 * Atom person element
 */
class XML_Atom_Person extends _XML_Atom 
{
    protected $name;
    protected $email = null;
    protected $uri = null;
    
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
    
    protected function __construct()
    {
    }
    
    /**
     * create a XML_Atom_Feed by feed-node
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
}
?>