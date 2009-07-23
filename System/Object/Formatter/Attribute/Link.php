<?php
abstract class Formatter_Attribute_Link 
    extends _Formatter_Attribute 
    implements 
        Interface_Formatter_Attribute_TextSettable,
        Interface_Formatter_Attribute_HasLinkTarget        
{
    protected $text = '';
    protected $targetView = '';
    protected $targetFrame = '';
        
    public function setText($text)
    {
        $this->text = strval($text);
    }

    public function getText($text)
    {
        return $this->text;
    }
    
    abstract function getLinkAlias();
        
    public function toXHTML($insertString = null)
    {
        $insertString == null ? htmlentities($this->getText(), ENT_QUOTES, CHARSET) : $insertString;
        $str = '';
        try{
            if(!empty($this->targetView))
            {
                $v = VSpore::byName($this->targetView);
                $link = $v->LinkTo($this->getLinkAlias());
                $str = sprintf(
                	"<a href=\"%s\"%s>%s</a>\n"
                    ,$link
                    ,empty($this->targetFrame) ? '' : ' target="'.htmlentities($this->targetFrame,ENT_QUOTES,CHARSET).'"'
                    ,$insertString
                );
            }
        }
        catch (Exception $e)
        {
            return '';
        }
        return parent::toXHTML($str);
    }
}
?>