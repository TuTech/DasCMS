<?php
/**
 * Inline XHTML
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _XML_Atom_Content
 */
class XML_Atom_Content_InlineXHTML extends _XML_Atom_Content implements Interface_XML_Atom_TextContent, Interface_XML_Atom_ToDOMXML
{
    /**
     * create a XML_Atom_Content_InlineXHTML by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Content_InlineXHTML
     */
    public static function fromNode(DOMNode $node)
    {
        $content = new XML_Atom_Content_InlineXHTML();
        $content->parseNodeAttributes($node, _XML_Atom_Content::$_attributes);
        $content->data = strval($node->nodeValue);
        return $content;
    } 
    
    public function getText()
    {
        return $this->data;
    } 
    
    /**
     * @return XML_Atom_Content_InlineXHTML
     */
    public static function create($data)
    {
        $o = new XML_Atom_Content_InlineXHTML();
        $o->data = $data;
        return $o;
    }
    
    public function toXML(DOMDocument $doc, $elementName)
    {
        $node = $doc->createElement($elementName);
        $frac = $doc->createDocumentFragment();
        @$frac->appendXML('<div xmlns="http://www.w3.org/1999/xhtml">'.$this->data.'</div>');
        $ent = $frac->entries;
        if($ent && $ent->item(0))
        {
            $node->appendChild($ent->item(0));
        }
        return $node;
    }
}
?>