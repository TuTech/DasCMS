<?php
class XML_AtomParser extends _XML
{
    /**
     * @var DOMDocument
     */
    protected $DOMDocument;
    /**
     * @var _XML_Atom
     */
    protected $ObjectTree;
    
    protected $type;
    
    /**
     * constructor for parser
     *
     * @param DOMDocument $doc
     */
    public function __construct(DOMDocument $doc)
    {
        $this->DOMDocument = $doc;
        $this->type = $doc->documentElement->localName;
        if($this->type == 'feed')
        {
            $this->debug_log('loading feed');
            $this->ObjectTree = XML_Atom_Feed::fromNode($doc->documentElement);
        }
        elseif($this->type == 'entry')
        {
            $this->debug_log('loading entry');
            $this->ObjectTree = XML_Atom_Entry::fromNode($doc->documentElement);
        }
        else
        {
            throw new Exception('xml is not Atom 1.0', 1);
        }
    }
    
    /**
     * get the parsed tree
     * 
     * @return _XML_Atom
     */
    public function getObjectTree()
    {
        return $this->ObjectTree;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @return XML_Atom_Feed
     */
    public function getFeedTree()
    {
        if($this->type != 'feed')
        {
            throw new Exception('not a feed');
        }
        return $this->ObjectTree;
    }
    
    /**
     * @return XML_Atom_Entry
     */
    public function getEntryTree()
    {
        if($this->type != 'entry')
        {
            throw new Exception('not an entry');
        }
        return $this->ObjectTree;
    }
    
    /**
     * create a XML_AtomParser for a xml-string
     * 
     * @param string $string
     * @return XML_AtomParser
     */
    public static function ParseString($string)
    {
        return new XML_AtomParser(_XML::loadString($string));
    }
    
    /**
     * create a XML_AtomParser for a xml-file
     * 
     * @param string $file
     * @return XML_AtomParser
     */
    public static function ParseFile($file)
    {
        return new XML_AtomParser(_XML::loadFile($file));
    }
}
?>