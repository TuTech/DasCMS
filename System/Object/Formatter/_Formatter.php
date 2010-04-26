<?php
abstract class _Formatter extends _
{
    protected $persistentAttributes = array();
    protected $tagsPreventingVisibility = array();
    protected $tagsAllowingVisibility = array();
    
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
    
    public function __sleep()
    {
        return array_unique($this->getPersistentAttributes());
    }
    
    protected function getPersistentAttributes(array $add = array())
    {
        return array_merge($this->persistentAttributes, $add, array('tagsPreventingVisibility', 'tagsAllowingVisibility'));
    }
    
    protected function isVisible()
    {
        $visible = true;
        //needs at least one matching tag to be shown
        if(count($this->tagsAllowingVisibility) > 0)
        {
            $tags = $this->getContent()->getTags();
            $visible = count(array_intersect($tags, $this->tagsAllowingVisibility)) > 0;
        }
        //one intersection here and it will be hidden
        if($visible && count($this->tagsPreventingVisibility) > 0)
        {
            $tags = $this->getContent()->getTags();
            $visible = count(array_intersect($tags, $this->tagsPreventingVisibility)) == 0;
        }
        return $visible;
    }
    
    /**
     * @return BContent
     */
    protected function getContent()
    {
        if(!$this->targetContent instanceof BContent)
        {
            throw new XUndefinedException('no content');
        }
        return $this->targetContent;
    }
    
    
}
?>