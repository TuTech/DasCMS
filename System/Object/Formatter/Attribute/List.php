<?php
abstract class Formatter_Attribute_List
    extends _Formatter_Attribute
{
    abstract protected  function getListEntries();
    
    public function toXHTML($insertString = null)
    {
        //for linked list parent call
        if($insertString == null)
        {
            $str = "<ul>\n";
            foreach ($this->getListEntries() as $entry)
            {
                $str .= "<li>".$this->escapeString($entry)."</li>\n";
            }
            $str .= "</ul>\n";
        }
        else
        {
            $str = $insertString;
        }
        return parent::toXHTML($str);
    }
}
?>