<?php
abstract class _View_iCalendar extends _View
{
    const NL = "\r\n";

    protected $sectionName = null;
    
    abstract protected function getSectionBody();
    
    protected function makeDateString($date, $utc = true)
    {
        return $utc ? gmdate("Ymd\THis\Z",intval($date)) : date("Ymd\THis",intval($date));
    }
    
    protected function makeDateCommand($cmd, $time, $utc = true)
    {
        $time = $this->makeDateString($time, $utc);
        $cmd = $utc ? $cmd : $cmd.';TZID='.LConfiguration::get('timezone');
        return $this->makeCommand($cmd, $time, false);
        
    }
    
    protected function makeCommand($cmd, $value, $escape = false)
    {
        if($escape)
        {
            $value = $this->escapeString($value);
        }
        $cmd = sprintf('%s:%s%s', $cmd, $value, _View_iCalendar::NL);
        $cmd = rtrim(chunk_split($cmd,72,_View_iCalendar::NL.' '), _View_iCalendar::NL.' ')._View_iCalendar::NL;
        return $cmd;
    }
    
    protected function escapeString($str)
    {
        $replace = array("\n" => "\\n", "\r" => "\\r", "," => "\\,");
        foreach ($replace as $char => $with)
        {
            $str = str_replace($char,$with, $str);
        }
        return $str;
    }
    
    public function __toString()
    {
        $str = $this->makeCommand('BEGIN', $this->sectionName);
        $str .= $this->getSectionBody();
        $str .= $this->makeCommand('END', $this->sectionName);
        return $str;
    }
} 
?>