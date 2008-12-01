<?php
/**
 * detect content type and create a matching _XML_Atom_Content object
 */
class XML_Atom_Content_Detector extends _XML_Atom_Content 
{
    protected $src = null; 
    protected static $_attributes = array(
        'type' => _XML::NONE_OR_MORE,
        'src' => _XML::NONE_OR_MORE
        );
        
    /**
     * create a _XML_Atom_Content by node
     *
     * @param DOMNode $node
     * @return _XML_Atom_Content
     */
    public static function fromNode(DOMNode $node)
    {
        $detector = new XML_Atom_Content_Detector();
        $detector->parseNodeAttributes($node, self::$_attributes);
        //external file
        if($detector->src != null)
        {
            return XML_Atom_Content_OutOfLine::fromNode($node);
        }
        //defined xhtml
        if($detector->type == 'xhtml')
        {
            return XML_Atom_Content_InlineXHTML::fromNode($node);
        }
        //defined html or text
        if($detector->type == 'html' || $detector->type == 'text')
        {
            return XML_Atom_Content_InlineText::fromNode($node);
        }
        //undefined in non-text nodes
        if( 
            preg_match('.+/.+',$detector->type) 
            || (
                $node->hasChildNodes() 
                && (
                    $node->childNodes->length > 1
                    || $node->firstChild->nodeType != XML_TEXT_NODE
                )
            )
        )
        {
            return XML_Atom_Content_Other::fromNode($node);
        }
        else
        //undefined in text-node or empty
        {
            return XML_Atom_Content_InlineText::fromNode($node);
        }
    }
}
?>