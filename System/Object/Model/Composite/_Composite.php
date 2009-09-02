<?php
abstract class _Model_Composite extends _Model
{
    /**
     * @var BContent
     */
    protected $compositeFor;
    
    public function __construct(BContent $compositeFor)
    {
        $this->compositeFor = $compositeFor;
    }
}
?>