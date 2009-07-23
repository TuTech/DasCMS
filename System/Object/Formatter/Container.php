<?php
class Formatter_Container extends _Formatter
{
    protected static $availableAttributes = null;
    protected $attachedAttributes = array();
    protected $uniqueName = null;
    
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
            $str .= strval($attribute);
        }
        $str.= "</div>\n";
    }
    
    public function __sleep()
    {
        return array('uniqueName', 'attachedAttributes');
    }
    
    public function freeze()
    {
        return serialize($this);
    }
    
    /**
     * @param string $data
     * @return Formatter_Container
     */
    public static function unfreeze($data)
    {
        //reverse evil
        $container = unserialize($data);
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
    public static function unfreezeForFormatting($data, BContent $content)
    {
        $obj = self::unfreeze($data);
        $obj->setTargetContent($content);
        return $obj;
    }
}
?>