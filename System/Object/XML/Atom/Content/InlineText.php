<?php
/**
 * Inline text content
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _XML_Atom_Content
 */
class XML_Atom_Content_InlineText extends _XML_Atom_Content implements Interface_XML_Atom_TextContent, Interface_XML_Atom_ToDOMXML
{
    /**
     * create a XML_Atom_Content_InlineText by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Content_InlineText
     */
    public static function fromNode(DOMNode $node)
    {
        $content = new XML_Atom_Content_InlineText();
        $content->parseNodeAttributes($node, _XML_Atom_Content::$_attributes);
        $content->data = strval($node->nodeValue);
        return $content;
    } 
    
    public function getText()
    {
        return $this->data;
    }    
    
    /**
     * @return XML_Atom_Content_InlineText
     */
    public static function create($data, $type = 'text')
    {
        $o = new XML_Atom_Content_InlineText();
        $o->data = $data;
        $o->type = $type;
        return $o;
    } 
}
?>