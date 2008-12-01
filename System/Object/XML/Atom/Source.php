<?php
/**
 * Atom source element - preserves the meta data from an other feed
 */
class XML_Atom_Source extends XML_Atom_Feed 
{
    protected function __construct()
    {
    }
    
    /**
     * create a XML_Atom_Source by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Source
     */
    public static function fromNode(DOMNode $node)
    {
        $source = new XML_Atom_Source();
        $source->parseNodeElements($node, XML_Atom_Feed::$_elements);
        return $source;
    }
}
?>