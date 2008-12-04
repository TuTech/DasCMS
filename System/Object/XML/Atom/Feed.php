<?php
/**
 * Atom feed element
 */
class XML_Atom_Feed extends _XML_Atom implements Interface_XML_Atom_ToDOMXML
{
    //attributes
    protected 
        $xmlns = "http://www.w3.org/2005/Atom";
    //elements
    protected
        $c__author,
        $c__category,
        $c__contributor,
        $c__generator,
        $c__icon,
        $c__id,
        $c__link,
        $c__logo,
        $c__rights,
        $c__subtitle,
        $c__title,
        $c__updated,
        $c__entry;
        
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
    
    protected function getElementDefinition()
    {
        return self::$_elements;
    }
    
    protected function getAttributeDefinition()
    {
        return array('xmlns' => _XML::EXACTLY_ONE);
    }
    
    protected function __construct()
    {
    }
    
    /**
     * create a XML_Atom_Feed by node
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
            
    /**
     * @return Collection_List_Atom_Person
     */
    public function getAuthors()
    {
        return new Collection_List_Atom_Person($this->c__author);
    } 
    /**
     * @return Collection_List_Atom_Person
     */  
    public function getContributors()
    {
        return new Collection_List_Atom_Person($this->c__contributor);
    } 
        
    /**
     * @return Collection_List_Atom_Category
     */
    public function getCategories()
    {
        return new Collection_List_Atom_Category($this->c__category);
    } 
        
    /**
     * @return Collection_List_Atom_Link
     */
    public function getLinks()
    {
        return new Collection_List_Atom_Link($this->c__link);
    } 
        
    /**
     * @return Collection_List_Atom_Entry
     */
    public function getEntries()
    {
        return new Collection_List_Atom_Entry($this->c__entry);
    } 
        
    /**
     * @return XML_Atom_Generator
     */
    public function getGenerator()
    {
        return $this->getFirstChild('generator');
    } 
        
    /**
     * @return XML_Atom_Text
     */
    public function getId()
    {
        return $this->getFirstChild('id');
    } 
        
    /**
     * @return XML_Atom_Text
     */
    public function getIcon()
    {
        return $this->getFirstChild('icon');
    }  
       
    /**
     * @return XML_Atom_Text
     */
    public function getRights()
    {
        return $this->getFirstChild('rights');
    }  
       
    /**
     * @return XML_Atom_Text
     */
    public function getLogo()
    {
        return $this->getFirstChild('logo');
    } 
        
    /**
     * @return XML_Atom_Text
     */
    public function getSubTitle()
    {
        return $this->getFirstChild('subtitle');
    } 
        
    /**
     * @return XML_Atom_Text
     */
    public function getTitle()
    {
        return $this->getFirstChild('title');
    }  
       
    /**
     * @return XML_Atom_Date
     */
    public function getUpdated()
    {
        return $this->getFirstChild('updated');
    }     
}
?>