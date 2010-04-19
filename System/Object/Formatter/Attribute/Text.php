<?php
abstract class Formatter_Attribute_Text
    extends _Formatter_Attribute
{
    public function toXHTML($insertString = null)
    {
        $str = sprintf(
        	"<div class=\"text\">%s</div>\n",
            $insertString
        );
        return parent::toXHTML($str);
    }
}
?>