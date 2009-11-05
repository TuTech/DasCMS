<?php
abstract class _Model_Content_Composite extends _Model_Content
{
    /**
     * @var Interface_Content
     */
    protected $compositeFor;
    
    public function __construct(Interface_Content $compositeFor)
    {
        $this->compositeFor = $compositeFor;
    }
}
?>