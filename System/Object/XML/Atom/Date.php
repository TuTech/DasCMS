<?php
/**
 * Atom date element
 */
class XML_Atom_Date extends _XML_Atom 
{
    protected $timestamp = 0;
    protected $string = '';    
    
    
    protected static $_elements = array(
    );
     
    private static $_elementParser = array(
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
     * @return XML_Atom_Date
     */
    public static function fromNode(DOMNode $node)
    {
        $date = new XML_Atom_Date();
        $date->string = $node->textContent;
        $date->timestamp = strtotime($node->textContent);
        return $date;
    }
    
    public function __toString()
    {
        return strval(date('c', $this->timestamp));
    }
}
?>