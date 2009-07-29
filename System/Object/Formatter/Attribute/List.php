<?php
abstract class Formatter_Attribute_List
    extends Formatter_Attribute_Info
    implements Interface_Formatter_Attribute_TextSettable
{
    abstract protected  function getListEntries();
    protected $persistentAttributes = array('textAfter','textBefore','separator');
    
    protected $separator = '';
    
    public function getText()
    {
        return $this->separator;
    }
    
    public function setText($text)
    {
        $this->separator = $text;
    }
    
    public function toXHTML($insertString = null)
    {
        //for linked list parent call
        if($insertString == null)
        {
            $vals = array_values($this->getListEntries());
            if(count($vals) > 0)
            {
                $str = "<ul>\n";
                $sep = $this->separator != '' ?  '<span class="sep">'.$this->separator.'</span>':'';
                
                for($i = 0; $i < count($vals); $i++)
                {
                    if($i == count($vals)-1)
                    {
                        $sep = '';
                    }
                    $str .= "<li>".$this->escapeString($vals[$i]).$sep."</li>\n";
                }
                $str .= "</ul>\n";
            }
            else
            {
                return '';//abort here 
            }
        }
        else
        {
            $str = $insertString;
        }
        return parent::toXHTML($str);
    }
    
    
    public function toJSON(array $parentData = array())
    {
        if(!isset($parentData['data']))
        {
            $parentData['data'] = array();
        }
        $parentData['data']['text'] = $this->getText();
        return parent::toJSON($parentData);
    }
}
?>