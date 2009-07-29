<?php
class Formatter_Container 
    extends _Formatter
    implements 
        Interface_View_XHTML,
        Interface_View_JSON,
        Interface_View_Atom
{
    protected static $availableAttributes = null;
    protected $attachedAttributes = array();
    protected $uniqueName = null;
    protected $persistentAttributes = array('uniqueName', 'attachedAttributes');
    /**
     * @var BContent
     */
    protected $targetContent = null;
    
    protected static $Formatters = array();
    
    public function __construct($uniqueName)
    {
        $this->uniqueName = $uniqueName;
    }
    
    public function setTargetContent(BContent $content)
    {
        $this->targetContent = $content;
    }
    
    public function resetAttributes()
    {
        $this->attachedAttributes = array();
    }
    
    public function attachAttribute(_Formatter_Attribute $attribute)
    {
        $this->attachedAttributes[] = $attribute;
    }
    
    public function getAttachedAttributes()
    {
        return $this->attachedAttributes;
    }
    
    public function getAvailableAttributes()
    {
        if(!is_array(self::$availableAttributes))
        {
            self::$availableAttributes = array();
            //EGatherFormaterAttributes
            //IFormaterAttributeProvider: -getRestoreHash()/+restoreFromHash()
        }
        return self::$availableAttributes;
    }

    public function toJSON()
    {
        $data = array(
            'name' => $this->uniqueName,
            'attributes' => array()
        );
        foreach ($this->attachedAttributes as $attribute) 
        {
        	$data['attributes'][] = $attribute->toJSON();
        }
        print_r($data);
        return json_encode($data);
    }
    
    public function toXHTML()
    {
        //IF has content to format format content - else show config
        $str = '';
        if($this->isVisible())
        {
            $str = "<div class=\"".htmlentities($this->uniqueName, ENT_QUOTES, CHARSET)."\">\n";
            foreach ($this->attachedAttributes as $attribute)
            {
                $attribute->setParentContainer($this);
                $str .= strval($attribute);
            }
            $str.= "</div>\n";
        }
        return $str;
    }
    
    public function getAtomTag()
    {
        return 'entry';
    }
    
    /**
     * @return XML_Atom_Entry
     * (non-PHPdoc)
     * @see System/Object/Interface/View/Interface_View_Atom#toAtom()
     */
    public function toAtom()
    {
        $entry = XML_Atom_Entry::createWriteableInstance();
        foreach ($this->attachedAttributes as $attribute)
        {
            if($attribute instanceof Interface_View_Atom)
            {
                $entry->addElement($attribute->getAtomTag(), $attribute->toAtom());
            }
            $attribute->setParentContainer($this);
        }
        return $entry;
    }
    
    public function __toString()
    {
        return $this->toXHTML(); 
    }
    
    protected static function makeFileName($name)
    {
        if(!preg_match('/[0-9a-z_-]+/ui',$name))
        {
            throw new XInvalidDataException('name contains illegal chars or is empty');
        }
        return SPath::CONFIGURATION.'/FORMAT_'.$name.'.php';
    }
    
    public function freeze()
    {
        $file = self::makeFileName($this->uniqueName);
        DFileSystem::SaveData($file, $this);
    }

    
    /**
     * @param string $data
     * @return Formatter_Container
     */
    public static function unfreeze($name)
    {
        //reverse evil
        $file = self::makeFileName($name);
        $container = DFileSystem::LoadData($file);
        if(!$container instanceof Formatter_Container)
        {
            throw new XArgumentException('invalid data - not a container');
        }
        return $container;
    }
    
    /**
     * @param string $data
     * @param BContent $content
     * @return Formatter_Container
     */
    public static function unfreezeForFormatting($name, BContent $content)
    {
        if(!array_key_exists($name, self::$Formatters))
        {
            self::$Formatters[$name] = self::unfreeze($name);
        }
        $obj = clone self::$Formatters[$name];
        $obj->setTargetContent($content);
        return $obj;
    }
}
?>