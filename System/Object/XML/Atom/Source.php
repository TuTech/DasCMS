<?php
/**
 * Atom source element - preserves the meta data from an other feed
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _XML_Atom
 */
class XML_Atom_Source extends XML_Atom_Feed implements Interface_XML_Atom_ToDOMXML
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