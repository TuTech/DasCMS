<?php
abstract class _Model_Content_Composite extends _Model_Content
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