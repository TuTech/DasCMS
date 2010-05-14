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
	    QContentFormatter::setFormatter($this->compositeFor->getId(), $formatter);
	} 
	
	public function getChildContentFormatter()
	{
	    if($this->formatterName === null)
	    {
	        $this->formatterName = false;
    	    $res = QContentFormatter::getFormatterName($this->compositeFor->getId());
    	    if($res->getRowCount() == 1)
    	    {
    	        list($this->formatterName) = $res->fetch(); 
    	    }
    	    $res->free();
	    }
	    return $this->formatterName;
	}
	
	public function formatChildContent(Interface_Content $content)
	{
        $f = $this->getChildContentFormatter();
        if($f)    
        {
            return Formatter_Container::unfreezeForFormatting($f, $content);
        }
        else
        {
            throw new XUndefinedException('no formatter');
        }
	}
} 
?>