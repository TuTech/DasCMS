<?php
abstract class _View_Content_Calendar_XHTML
    extends _View_Content_Calendar 
{
    protected $tag = 'div';
    
    protected function withTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }
    
    protected function makeDateString($date, $utc = true)
    {
        return date("c",intval($date));
    }
    
    protected function makeDateCommand($cmd, $time, $utc = true)
    {
        return sprintf(
        	"\t\t".'<abbr class="%s" title="%s">%s</abbr>'."\n"
            ,strtolower($cmd)
            ,$this->makeDateString($time)
            ,date(Core::settings()->get('dateformat'),$time)
        );
    }
    
    protected function makeCommand($cmd, $value, $escape = false)
    {
        if($escape)
        {
            $value = $this->escapeString($value);
        }
        return sprintf("\t\t".'<%s class="%s">%s</%s>'."\n", $this->tag, strtolower($cmd), $value, $this->tag);
    }
    
    protected function escapeString($str)
    {
        return htmlentities($str, ENT_QUOTES, CHARSET);
    }
    
    public function __toString()
    {
        $str = sprintf("\n<%s class=\"%s\">\n", $this->tag, $this->sectionName);
        $str .= $this->getSectionBody();
        $str .= sprintf("</%s>\n", $this->tag);
        return $str;
    }
}
?>