<?php
/**
 * Atom entry element
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _XML_Atom
 */
class XML_Atom_Entry extends _XML_Atom implements Interface_XML_Atom_ToDOMXML
{
    protected 
        $c__author,
        $c__category,
        $c__content,
        $c__contributor,
        $c__id,
        $c__link,
        $c__published,
        $c__rights,
        $c__source,
        $c__summary,
        $c__title,
        $c__updated;
        
    protected $readOnly = true;
    
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
     
    /**
     * @return XML_Atom_Entry
     */
    public static function createWriteableInstance()
    {
        $o = new XML_Atom_Entry();
        $o->readOnly = false;
        return $o;
    } 
    
    public function validateElement($tag, _XML_Atom $element)
    {
        $tag = strtolower($tag);
        $requiredInstance = ($tag == 'content') 
            ? '_XML_Atom_Content' 
            : self::$_elementParser[$tag];
        return (
            array_key_exists($tag, self::$_elementParser) 
            && $element instanceof $requiredInstance
        );
    }
    
    /**
     * inits the attribute for the tag and returns true if an element can be added
     * @param $tag
     * @return bool
     */
    protected function initElement($tag)
    {
        $tag = strtolower($tag);
        if(!array_key_exists($tag, self::$_elements))
        {
            throw new XUndefinedException('unknown tag');
        }
        $att = 'c__'.$tag;
        if($this->{$att} == null)
        {
            $this->{$att} = array();
        }
        $type = self::$_elements[$tag]; 
        
        return !(($type == _XML::EXACTLY_ONE || $type == _XML::NONE_OR_ONE) && count($this->{$att}) > 0);
    }
    
    public function addElement($tag, _XML_Atom $element)
    {
        $tag = strtolower($tag);
        if($this->readOnly)
        {
            throw new XFileLockedException('this element has been created in read-only-mode');
        }      
        if($this->validateElement($tag, $element))
        {
            throw new XArgumentException('incompatible element given');
        }
        $writeable = $this->initElement($tag);
        if($writeable)
        {
            $att = 'c__'.$tag;
            $this->{$att}[] = $element;
        }
    }
    
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
        return array();
    }
    
    /**
     * @param IGeneratesFeed $feed
     * @param BContent $content
     * @return XML_Atom_Entry
     */
    public static function fromContent(IGeneratesFeed $feed, BContent $content)
    {
        $o = new XML_Atom_Entry();
        //author?
        $o->c__author = array(XML_Atom_Person::create($content->getCreatedBy()));
        //id
        $o->c__id = array(XML_Atom_Text::create(SLink::base().$content->getGUID()));
        //category*
        $o->c__category = array();
        foreach ($content->getTags() as $tag) 
        {
        	if(!empty($tag))$o->c__category[] = XML_Atom_Category::create($tag);
        }
        //content?
        if($content instanceof Interface_XML_Atom_ProvidesInlineXHTML)
        {
            $o->c__content = array(XML_Atom_Content_InlineXHTML::create($content->getInlineXHTML()));
        }
        elseif($content instanceof Interface_XML_Atom_ProvidesInlineText)
        {
            $o->c__content = array(XML_Atom_Content_InlineText::create($content->getInlineText(), $content->getInlineTextType()));
        }
        elseif($content instanceof Interface_XML_Atom_ProvidesOutOfLineContent)
        {
            $o->c__content = array(XML_Atom_Content_OutOfLine::create($content->getOutOfLineType(), $content->getOutOfLineURI()));
        }
        //link*
        $q = $feed->getFeedTargetView();
        $linker = $content->getAlias();
        if(VSpore::exists($q))
        {
            $linker = new VSpore($q);
            $linker->LinkTo($content->getAlias());
        }
        $o->c__link = array(
            XML_Atom_Link::create(SLink::base().strval($linker), 'alternate', 'application/xml+xhtml')
        );
        if($content instanceof IFileContent)
        {
            list($filename, $type, $size) = $content->getDownloadMetaData();
            $o->c__link[] = XML_Atom_Link::create(SLink::base().'file.php/'.$content->getAlias(), 'enclosure', $type, null, $content->getFileName(), $size);
        }
        //title
        $o->c__title = array(XML_Atom_Text::create($content->getTitle()));
        //updated
        $o->c__updated = array(XML_Atom_Date::create($content->getModifyDate()));
        //published?
        $o->c__published = array(XML_Atom_Date::create($content->getPubDate()));
        //summary?
        $o->c__summary = array(XML_Atom_Text::create($content->getDescription(),'html'));
        return $o;
    }
    
    /**
     * create a XML_Atom_Entry by node
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
     * @return _XML_Atom_Content
     */
    public function getContent()
    {
        return $this->getFirstChild('content');
    } 
        
    /**
     * @return XML_Atom_Text
     */
    public function getId()
    {
        return $this->getFirstChild('id');
    } 
        
    /**
     * @return XML_Atom_Date
     */
    public function getPublished()
    {
        return $this->getFirstChild('published');
    }  
       
    /**
     * @return XML_Atom_Text
     */
    public function getRights()
    {
        return $this->getFirstChild('rights');
    }  
       
    /**
     * @return XML_Atom_Source
     */
    public function getSource()
    {
        return $this->getFirstChild('source');
    } 
        
    /**
     * @return XML_Atom_Text
     */
    public function getSummary()
    {
        return $this->getFirstChild('summary');
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