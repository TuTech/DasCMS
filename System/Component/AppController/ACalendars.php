<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-07-21
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class ACalendars
    extends 
        BAContentAppController
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog
{
    /**
     * required permission for class
     * @var string
     */
    protected $contentPermission = 'org.bambuscms.content.ccalendar';
    
    /**
     * content class
     * @var string
     */
    protected $contentClass = 'CCalendar';
        
    /**
     * content icon
     * @var string
     */
    protected $contentIcon = 'ics';
    
    /**
	 * @var CScript
     */
    protected $target = null;
    
    const GUID = 'org.bambuscms.applications.calendars';
    
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function save(array $param)
    {
        $this->checkPermission('change');
        if($this->target != null)
        {
            if(isset($param['content']))
            {
                $this->target->RAWContent = $param['content'];
            }
            if(!empty($param['title']))
            {
                $this->target->Title = $param['title'];
            }
            if(isset($param['subtitle']))
            {
                $this->target->SubTitle = $param['subtitle'];
            }
            if(isset($param['formatter']))
            {
                $this->target->setChildContentFormatter($param['formatter']);
            }
            if(isset($param['aggregator']))
            {
                $this->target->setContentAggregator($param['aggregator']);
            }
        }
    }
}
?>