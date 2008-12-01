<?php
/**
 * Some other inline content
 */
class XML_Atom_Content_Other extends _XML_Atom_Content
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
}
?>