<?php
class WLink extends BWidget 
{
    const WELLFORMABLE = 0;
    const FORCE_STANDARD = 1;
    
    private $title = 'Link';
    private $urlData = array();
    private $mode = self::WELLFORMABLE;
    private $target = '_self';
    private static $globalURLData = null;
    
    public function __construct(array $data, $title = null, $target = null, $mode = null)
    {
        
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    public function getMode()
    {
        return $this->mode;
    }
    
    public function setMode($mode)
    {
        if($mode == self::WELLFORMABLE || $mode == self::FORCE_STANDARD)
        {
            $this->mode = $mode;
        }
        else
        {
            throw new XInvalidDataException('unknown mode');
        }
    }
    
    public function getTarget()
    {
        return $this->target;
    }
    
    public function setTarget($target)
    {
        $this->target = $target;
    }
    
    public function set($key, $value)
    {
        $this->urlData[$key] = $value; 
    }
    
    public function get($key)
    {
        if(array_key_exists($key, $this->urlData))
        {
            return $this->urlData[$key];
        }
        else
        {
            throw new XUndefinedIndexException();
        }
    }
    
    
    public function getURL()
    {
        //use SLink
    }
    
    public function render()
    {
        return sprintf(
            '<a href="%s" target="%s">%s</a>'
            ,$this->getURL()
            ,$this->target
            ,$this->title
        );
    }
}

?>