<?php
class XML_Atom_Content_InlineText extends _XML_Atom_Content
{
    /**
     * create a XML_Atom_Feed by feed-node
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
}
?>