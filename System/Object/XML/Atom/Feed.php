<?php
/**
 * Atom feed element
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _XML_Atom
 */
class XML_Atom_Feed extends _XML_Atom implements Interface_XML_Atom_ToDOMXML
{
    //attributes
    protected 
        $xmlns = "http://www.w3.org/2005/Atom";
    //elements
    protected
        $c__author = array(),
        $c__category = array(),
        $c__contributor = array(),
        $c__generator = array(),
        $c__icon = array(),
        $c__id = array(),
        $c__link = array(),
        $c__logo = array(),
        $c__rights = array(),
        $c__subtitle = array(),
        $c__title = array(),
        $c__updated = array(),
        $c__entry = array();
        
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
     * @param CFeed $content
     * @return XML_Atom_Feed
     */
    public static function fromContent(CFeed $content)
    {
        $o = new XML_Atom_Feed();
        $o->xml_base = SLink::base();
        $o->c__author = array(XML_Atom_Person::create($content->getCreatedBy()));
        $o->c__generator = array(XML_Atom_Generator::create(BAMBUS_VERSION_NAME, BAMBUS_VERSION_NUMBER, 'http://www.bambus-cms.org'));
        $o->c__id = array(XML_Atom_Text::create(SLink::base().$content->getGUID()));
        $o->c__category = array();
        foreach ($content->getTags() as $tag) 
        {
        	if(!empty($tag))$o->c__category[] = XML_Atom_Category::create($tag);
        }
        $q = $content->option(CFeed::SETTINGS, 'TargetView');
        $linker = $content->getAlias();
        if(VSpore::exists($q))
        {
            $linker = new VSpore($q);
            $linker->LinkTo($content->getAlias());
        }
        $o->c__link = array(
            XML_Atom_Link::create(SLink::base().strval($linker), 'alternate', 'application/xml+xhtml'),
            XML_Atom_Link::createSelfLink()
        );
        $copyright = LConfiguration::get('copyright');
        if(!empty($copyright))
        {
            $o->c__rights = array(XML_Atom_Text::create($copyright));
        }
        $o->c__title = array(XML_Atom_Text::create($content->getTitle()));
        $o->c__subtitle = array(XML_Atom_Text::create($content->getSubTitle(),'html'));
        $o->c__updated = array(XML_Atom_Date::create($content->getModifyDate()));
        $o->c__entry = array();
        return $o;
    }
    
    public function appendEntry(XML_Atom_Entry $entry)
    {
        $eguid = $entry->getId()->getText();
        $hasEntry = $this->c__id[0]->getText() == $eguid;
        foreach ($this->c__entry as $currententry) 
        {        
        	$hasEntry = $hasEntry || $currententry->getId()->getText() == $eguid;
        	if($hasEntry)break;
        }
        if(!$hasEntry)
        {
            $updated = $this->getFirstChild('updated')->getTimestamp();
            $childUpdated = $entry->getUpdated()->getTimestamp();
            if($updated < $childUpdated)
            {
                $this->c__updated[0] = XML_Atom_Date::create($childUpdated);
            }
            $this->c__entry[] = $entry;
        }
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