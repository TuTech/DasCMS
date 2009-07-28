<?php
abstract class _Formatter extends _
{
    protected $persistentAttributes = array();

    public function __sleep()
    {
        return array_unique($this->getPersistentAttributes());
    }
    
    protected function getPersistentAttributes(array $add = array())
    {
        return array_merge($this->persistentAttributes, $add);
    }
    
    /**
     * @return BContent
     */
    protected function getContent()
    {
        if($this->targetContent == null)
        {
            throw new XUndefinedException('no content');
        }
        return $this->targetContent;
    }
}
?>