<?php
/**
 * Inline XHTML
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
}
?>