<?php
class Factory_Calendar
    extends _
    implements IShareable
{
    const AS_FILE = 'File';
    const AS_XHTML = 'XHTML';
    
    protected static $inst = null;
    
    protected function __construct(){}
    
    /**
     * (non-PHPdoc)
     * @see System/Component/Interface/IShareable#getSharedInstance()
     * @return Factory_Calendar
     */
    public static function getSharedInstance()
    {
        if(self::$inst === null)
        {
            self::$inst = new Factory_Calendar();
        }
        return self::$inst;
    }
    
    public function createCalendar($as, $title)
    {
        switch ($as)
        {
            case self::AS_FILE: return new View_Content_Calendar_File_Calendar($title);
            case self::AS_XHTML:return new View_Content_Calendar_XHTML_Calendar($title);
            default: throw new XUndefinedException(); 
        }
    }
}
?>