<?php
abstract class _View_Content_Calendar 
    extends _View
{
    protected $sectionName = null;
    
    abstract protected function getSectionBody();
    
    abstract protected function makeDateString($date, $utc = true);
    
    abstract protected function makeDateCommand($cmd, $time, $utc = true);
    
    abstract protected function makeCommand($cmd, $value, $escape = false);
    
    abstract protected function escapeString($str);
}
?>