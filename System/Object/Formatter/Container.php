<?php
class Formatter_Container extends _Formatter
{
    protected static $availableAttributes = null;
    protected $attachedAttributes = array();
    protected $uniqueName = null;
    
    protected static $Formatters = array();
    
    public function __construct($uniqueName)
    {
        $this->uniqueName = $uniqueName;
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

    public function __toString()
    {
        
        //IF has content to format format content - else show config
        
        $str = "<div class=\"".htmlentities($this->uniqueName, ENT_QUOTES, CHARSET)."\">\n";
        foreach ($this->attachedAttributes as $attribute)
        {
            if($this->targetContent !== null)
            {
                $attribute->setTargetContent($this->targetContent);
            }
            $str .= strval($attribute);
        }
        $str.= "</div>\n";
        return $str;
    }
    
    public function __sleep()
    {
        return array('uniqueName', 'attachedAttributes');
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