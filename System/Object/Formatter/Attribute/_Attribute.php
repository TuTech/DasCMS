<?php
abstract class _Formatter_Attribute extends _Formatter
{
    protected $enabled = false;
    protected $title = null;//FIXME to be defined in extending class for display in config

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
        
    protected function createLink($alias, $htmlInLink)
    {
        try
        {
            $str = '';
            if(!empty($targetView))
            {
                $targetFrame = $this->getTargetFrame();
                $link = $this->getTargetView()->LinkTo($alias);
                $str = sprintf(
                	"<a href=\"%s\"%s>%s</a>\n"
                    ,$link
                    ,empty($targetFrame) ? '' : ' target="'.htmlentities($targetFrame,ENT_QUOTES,CHARSET).'"'
                    ,$htmlInLink
                );
            }
        }
        catch (Exception $e)
        {
            $str =  '';
        }
        return $str;
    }
    
    /**
     * @return string
     */
    abstract protected function getFormatterClass();
    
    /**
     * @return VSpore
     */
    protected function getTargetView()
    {
        //FIXME
        $targetView = 'page';
        return VSpore::byName($targetView);
    }
    protected function getTargetFrame()
    {
        //FIXME
        return null;
    }
    
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