<?php
abstract class _Model_Content_Composite 
	extends _
	implements Interface_Composites_Attachable
{
    /**
     * @var Interface_Content
     */
    protected $compositeFor;

    public function __construct(Interface_Content $compositeFor)
    {
        $this->compositeFor = $compositeFor;
    }

	public function attachedToContent(Interface_Content $content)
    {
        return $this->compositeFor->getId() == $content->getId();
    }

    public function contentSaves(){}
}
?>