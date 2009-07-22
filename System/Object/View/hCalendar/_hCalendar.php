<?php
abstract class _View_hCalendar extends _View
{
    protected $tag = 'div';
    protected $sectionName ;
    
    abstract protected function getSectionBody();
    
    protected function makeDateString($date)
    {
        return date("c",intval($date));
    }
    
    protected function makeDateCommand($cmd, $time)
    {
        return sprintf(
        	"\t\t".'<abbr class="%s" title="%s">%s</abbr>'."\n"
            ,strtolower($cmd)
            ,$this->makeDateString($time)
            ,date(LConfiguration::get('dateformat'),$time)
        );
    }
    
    protected function makeCommand($tag, $cmd, $value, $escape = false)
    {
        if($escape)
        {
            $value = $this->escapeString($value);
        }
        return sprintf("\t\t".'<%s class="%s">%s</%s>'."\n", $tag, strtolower($cmd), $value, $tag);
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