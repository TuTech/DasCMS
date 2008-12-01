<?php
/**
 * Atom date element
 */
class XML_Atom_Date extends _XML_Atom 
{
    protected $timestamp = 0;
    protected $string = '';    
    
    protected function __construct()
    {
    }
    
    /**
     * create a XML_Atom_Date by node
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
    
    public function getDateString()
    {
        return $this->string;
    }     
    
    public function getCDate()
    {
        return date('c', $this->timestamp);
    }     
    
    public function getTimestamp()
    {
        return $this->timestamp;
    }     
}
?>