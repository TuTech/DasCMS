<?php
/**
 * Inline text content
 *
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
}
?>