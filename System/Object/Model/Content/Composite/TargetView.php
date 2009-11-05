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
	        QBContent::removeViewBinding($this->compositeFor->getId());
	    }
	    else
	    {
	        QBContent::setViewBinding($this->compositeFor->getId(), $viewname);
	    }
	} 
	
	/**
	 * @return string|null 
	 */
	public function getTargetView()
	{
	    $res = QBContent::getViewBinding($this->compositeFor->getId());
	    $view = null;
	    if($res->getRowCount() == 1)
	    {
	        list($view) = $res->fetch(); 
	    }
	    $res->free();
	    return $view;
	}
	
} 
?>