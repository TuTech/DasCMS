<?php
abstract class _Formatter extends _
{
    protected $targetContent = null;
    
    protected function setTargetContent(BContent $content)
    {
        $this->targetContent = $content;
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