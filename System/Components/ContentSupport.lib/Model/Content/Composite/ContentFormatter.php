<?php
class Model_Content_Composite_ContentFormatter extends _Model_Content_Composite
{
    private $targetView = null;
    private $formatterName = null;
    
    public static function getCompositeMethods()
    {
        return array(
        	'getChildContentFormatter', 
        	'setChildContentFormatter',
            'formatChildContent'
        );
    }
    
    public function __construct(Interface_Content $compositeFor)
    {
        parent::__construct($compositeFor);
    }

    public function setChildContentFormatter($formatter)
	{
		$Db = Core::Database()->createQueryForClass($this);
		$Db->beginTransaction();
		$Db->call('unlink')
			->withParameters($this->compositeFor->getId())
			->execute();
		$Db->call('link')
			->withParameters($this->compositeFor->getId(), $formatter)
			->execute();
		$Db->commitTransaction();
	} 
	
	public function getChildContentFormatter()
	{
	    if($this->formatterName === null)
	    {
			$this->formatterName = Core::Database()
				->createQueryForClass($this)
				->call('contentFormatter')
				->withParameters($this->compositeFor->getId())
				->fetchSingleValue();
			if(empty($this->formatterName)){
				$this->formatterName = false;
			}
	    }
	    return $this->formatterName;
	}
	
	public function formatChildContent(Interface_Content $content)
	{
        $f = $this->getChildContentFormatter();
        if($f)    
        {
            return Controller_View::getInstance()->display($content, $f);
        }
        else
        {
            throw new XUndefinedException('no formatter');
        }
	}
} 
?>