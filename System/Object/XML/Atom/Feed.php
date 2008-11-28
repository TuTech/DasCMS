<?php
/**
 * Atom feed element
 */
class XML_Atom_Feed extends _XML_Atom 
{
    protected
        $author,
        $category,
        $contributor,
        $generator,
        $icon,
        $id,
        $link,
        $logo,
        $rights,
        $subtitle,
        $title,
        $updated,
        $entry;
        
    protected static $_elements = array(
        'author' 		=> _XML::NONE_OR_MORE,
        'category' 		=> _XML::NONE_OR_MORE,
        'contributor'	=> _XML::NONE_OR_MORE,
        'generator' 	=> _XML::NONE_OR_ONE,
        'icon' 			=> _XML::NONE_OR_ONE,
        'id' 			=> _XML::EXACTLY_ONE,
        'link' 			=> _XML::NONE_OR_MORE, 
        'logo' 			=> _XML::NONE_OR_ONE,
        'rights' 		=> _XML::NONE_OR_ONE,
        'subtitle' 		=> _XML::NONE_OR_ONE,
        'title' 		=> _XML::EXACTLY_ONE,
        'updated'    	=> _XML::EXACTLY_ONE,
        'entry'			=> _XML::NONE_OR_MORE
     );
     
    private static $_elementParser = array(
        'author' 		=> 'XML_Atom_Person',
        'category' 		=> 'XML_Atom_Category',
        'contributor'	=> 'XML_Atom_Person',
        'generator' 	=> 'XML_Atom_Generator',
        'icon' 			=> 'XML_Atom_Text',
        'id' 			=> 'XML_Atom_Text',
        'link' 			=> 'XML_Atom_Link', 
        'logo' 			=> 'XML_Atom_Text',
        'rights' 		=> 'XML_Atom_Text',
        'subtitle' 		=> 'XML_Atom_Text',
        'title' 		=> 'XML_Atom_Text',
        'updated'    	=> 'XML_Atom_Date',
        'entry'			=> 'XML_Atom_Entry'
     );
     
    protected function getElementParsers()
    {
        return self::$_elementParser;
    }
    
    protected function __construct()
    {
    }
    
    /**
     * create a XML_Atom_Feed by feed-node
     *
     * @param DOMNode $node
     * @return XML_Atom_Feed
     */
    public static function fromNode(DOMNode $node)
    {
        $feed = new XML_Atom_Feed();
        $feed->parseNodeElements($node, self::$_elements);
        return $feed;
    }
}
?>