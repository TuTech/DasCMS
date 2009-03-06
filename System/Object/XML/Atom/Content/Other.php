<?php
/**
 * Some other inline content
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _XML_Atom_Content
 */
class XML_Atom_Content_Other extends _XML_Atom_Content implements Interface_XML_Atom_ToDOMXML
{
    /**
     * create a XML_Atom_Content_Other by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Content_Other
     */
    public static function fromNode(DOMNode $node)
    {
        $content = new XML_Atom_Content_Other();
        $content->parseNodeAttributes($node, _XML_Atom_Content::$_attributes);
        $content->data = strval($node->nodeValue);
        return $content;
    }
    
    public function getData()
    {
        return $this->data;
    } 
}
?>