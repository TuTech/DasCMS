<?php
/**
 * Atom feed element
 */
class XML_Atom_Entry extends _XML_Atom 
{
    protected function __construct()
    {
    }
    
    protected static $_elements = array(
        'author' 		=> _XML::NONE_OR_MORE,
        'category'		=> _XML::NONE_OR_MORE,
        'content' 		=> _XML::NONE_OR_ONE,
        'contributor'	=> _XML::NONE_OR_MORE,
        'id' 			=> _XML::EXACTLY_ONE,
        'link' 			=> _XML::NONE_OR_MORE,
        'published'		=> _XML::NONE_OR_ONE,
        'rights'		=> _XML::NONE_OR_ONE,
        'source'		=> _XML::NONE_OR_ONE,
        'summary' 		=> _XML::NONE_OR_ONE,
        'title' 		=> _XML::EXACTLY_ONE,
        'updated' 		=> _XML::EXACTLY_ONE
    );

    private static $_elementParser = array(
        'author' 		=> 'XML_Atom_Person',
        'category'		=> 'XML_Atom_Category',
        'content' 		=> 'XML_Atom_Content_Detector',
        'contributor'	=> 'XML_Atom_Person',
        'id' 			=> 'XML_Atom_Text',
        'link' 			=> 'XML_Atom_Link',
        'published'		=> 'XML_Atom_Date',
        'rights'		=> 'XML_Atom_Text',
        'source'		=> 'XML_Atom_Source',
        'summary' 		=> 'XML_Atom_Text',
        'title' 		=> 'XML_Atom_Text',
        'updated' 		=> 'XML_Atom_Date'
    );
     
    protected function getElementParsers()
    {
        return self::$_elementParser;
    }
    
    /**
     * create a XML_Atom_Feed by feed-node
     *
     * @param DOMNode $node
     * @return XML_Atom_Entry
     */
    public static function fromNode(DOMNode $node)
    {
        $feed = new XML_Atom_Entry();
        $feed->parseNodeElements($node, self::$_elements);
        return $feed;
    }
}
?>