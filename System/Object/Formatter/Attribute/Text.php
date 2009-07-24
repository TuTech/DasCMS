<?php
abstract class Formatter_Attribute_Text
    extends _Formatter_Attribute 
{
    public function toXHTML($insertString = null)
    {
        $str = sprintf(
        	"<span class=\"text\">%s</span>\n", 
            $insertString
        );
        return parent::toXHTML($str);
    }
}
?>