<?php
class Model_Content_Composite_TargetView extends _Model_Content_Composite
{
    private $targetView = null;
    
    public static function getCompositeMethods()
    {
        return array('setTargetView', 'getTargetView');
    }
    
    public function __construct(Interface_Content $compositeFor)
    {
        parent::__construct($compositeFor);
    }

    public function setTargetView($viewname)
	{
	    //if name == '' -> delete
	    //else insert/update view
	    if(empty($viewname))
	    {
			Core::Database()
				->createQueryForClass($this)
				->call('removeViewBinding')
				->withParameters($this->compositeFor->getId())
				->execute();
	    }
	    else
	    {
			Core::Database()
				->createQueryForClass($this)
				->call('setViewBinding')
				->withParameters($this->compositeFor->getId(), $viewname, $viewname)
				->execute();
	    }
	} 
	
	/**
	 * @return string|null 
	 */
	public function getTargetView()
	{
		$res = Core::Database()
			->createQueryForClass($this)
			->call('getViewBinding')
			->withParameters($this->compositeFor->getId())
			->fetchSingleValue();

	    return empty($res) ? null : $res;
	}
	
} 
?>