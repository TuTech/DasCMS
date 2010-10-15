<?php
abstract class _Formatter 
{
	protected $_FormatterOwnAttributes = array(
		'tagsPreventingVisibility',
		'tagsAllowingVisibility',
		'contentClassesPreventingVisibility',
		'contentClassesAllowingVisibility'
	);
    protected $persistentAttributes = array();
    protected $tagsPreventingVisibility = array();
    protected $tagsAllowingVisibility = array();
	protected $contentClassesPreventingVisibility = array();
    protected $contentClassesAllowingVisibility = array();

    //tags accessors
    public function getTagsAllowingVisibility()
    {
        return $this->tagsAllowingVisibility;
    }

    public function setTagsAllowingVisibility(array $tags)
    {
        $this->tagsAllowingVisibility = $tags;
    }

    public function getTagsPreventingVisibility()
    {
        return $this->tagsPreventingVisibility;
    }

    public function setTagsPreventingVisibility(array $tags)
    {
        $this->tagsPreventingVisibility = $tags;
    }

    //classes accessors
    public function getContentClassesAllowingVisibility()
    {
        return $this->contentClassesAllowingVisibility;
    }

    public function setContentClassesAllowingVisibility(array $classes)
    {
        $this->contentClassesAllowingVisibility = $classes;
    }

    public function getContentClassesPreventingVisibility()
    {
        return $this->contentClassesPreventingVisibility;
    }

    public function setContentClassesPreventingVisibility(array $classes)
    {
        $this->contentClassesPreventingVisibility = $classes;
    }

    //end accessors

    public function __sleep()
    {
        return array_unique($this->getPersistentAttributes());
    }

    protected function getPersistentAttributes(array $add = array())
    {
        return array_merge($this->persistentAttributes, $add, $this->_FormatterOwnAttributes);
    }

    protected function isVisible()
    {
        $visible = true;
        $class = get_class($this->getContent());
        //needs at least one matching tag to be shown
        if(count($this->tagsAllowingVisibility) > 0)
        {
            $tags = $this->getContent()->getTags();
            $visible = count(array_intersect($tags, $this->tagsAllowingVisibility)) > 0;
        }
        if($visible && count($this->contentClassesAllowingVisibility) > 0)
        {
			$visible = in_array($class, $this->contentClassesAllowingVisibility);
        }
        //one intersection here and it will be hidden
        if($visible && count($this->tagsPreventingVisibility) > 0)
        {
            $tags = $this->getContent()->getTags();
            $visible = count(array_intersect($tags, $this->tagsPreventingVisibility)) == 0;
        }
        if($visible && count($this->contentClassesPreventingVisibility) > 0)
        {
			$visible = !in_array($class, $this->contentClassesPreventingVisibility);
        }
        return $visible;
    }

    /**
     * @return Interface_Content
     */
    protected function getContent()
    {
        if(!$this->targetContent instanceof Interface_Content)
        {
            throw new XUndefinedException('no content');
        }
        return $this->targetContent;
    }


}
?>