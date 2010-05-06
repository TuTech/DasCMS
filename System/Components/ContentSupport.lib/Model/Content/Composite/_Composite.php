<?php
abstract class _Model_Content_Composite extends _
{
    /**
     * @var BContent
     */
    protected $compositeFor;

    public function __construct(BContent $compositeFor)
    {
        $this->compositeFor = $compositeFor;
    }

    public function contentSaves(){}
}
?>