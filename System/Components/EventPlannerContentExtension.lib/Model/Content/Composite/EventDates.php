<?php
class Model_Content_Composite_EventDates 
    extends _Model_Content_Composite
//    implements Interface_Composites_Attachable,
//			   Interface_Composites_AutoAttach
{
    protected $startDate, $endDate;
    
    public static function getCompositeMethods()
    {
        return array('getEventStartDate', 'getEventEndDate');
    }    
    
    /**
     * @param Interface_Content $compositeFor
     * @param int $startDate
     * @param int $endDate
     * @return Model_Content_Composite_EventDates
     */
    public static function setEventDatesForContent(Interface_Content $compositeFor, $startDate, $endDate)
    {
        if(!is_int($startDate) || !is_int($endDate))
        {
            throw new XArgumentException('dates must be of type int');
        }
        $c = new Model_Content_Composite_EventDates($compositeFor);
        $c->startDate = $startDate;
        $c->endDate = $endDate;
        $compositeFor->attachComposite($c);
        return $c;
    }
    
    public function attachedToContent(Interface_Content $content)
    {
        return true;
    }
    
    public function __construct(Interface_Content $compositeFor)
    {
        parent::__construct($compositeFor);
    }
    
	/**
	 * @return int
	 */
	public function getEventStartDate()
	{
		return $this->startDate;
	}
	   
	/**
	 * @return int
	 */
	public function getEventEndDate()
	{
		return $this->endDate;
	}
} 
?>