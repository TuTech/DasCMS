<?php
abstract class _Formatter_Attribute extends _Formatter
{
    protected $enabled = false;
    protected $title = null;

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled == true;
    }
    
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
        
    /**
     * @return string
     */
    abstract protected function getFormatterClass();
    
    /**
     * @return string
     */
    public function toXHTML($insertString = null)
    {
        return sprintf("<div class=\"%s\">\n%s</div>\n", $this->getFormatterClass(), $insertString);
    }
    
    public function __toString()
    {
        return $this->toXHTML();
    }
}
?>