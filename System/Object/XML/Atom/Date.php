<?php
/**
 * Atom date element
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _XML_Atom
 */
class XML_Atom_Date extends _XML_Atom implements Interface_XML_Atom_ToDOMXML
{
    protected $timestamp = 0;
    protected $string = '';    
    
    protected function __construct()
    {
    }
    
    /**
     * @return XML_Atom_Date
     */
    public static function create($timestamp)
    {
        $o = new XML_Atom_Date();
        $o->timestamp = $timestamp;
        return $o;
    }
    
    protected function getElementDefinition()
    {
        return array();
    }
    
    protected function getAttributeDefinition()
    {
        return array();
    }
    
    protected function isDataNode()
    {
        return true;
    }
    
    protected function getNodeData()
    {
        return $this->getCDate();
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
    
    public function getOriginalDateString()
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