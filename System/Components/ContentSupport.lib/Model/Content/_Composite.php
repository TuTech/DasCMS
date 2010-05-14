<?php
abstract class _Model_Content_Composite 
	extends _
	implements Interface_Composites_Attachable
{
    /**
     * @var BContent
     */
    protected $compositeFor;

    public function __construct(BContent $compositeFor)
    {
        $this->compositeFor = $compositeFor;
    }

	public function attachedToContent(BContent $content)
    {
        return $this->compositeFor->getId() == $content->getId();
    }

    public function contentSaves(){}
}
?>