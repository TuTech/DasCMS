<?php
class Formatter_Attribute_View_ScopeNext
    extends Formatter_Attribute_Link
{
    public function getLinkAlias()
    {
        return $this->getContent()->getAlias();
    }
    
    protected function getFormatterClass()
    {
        return 'ScopeNext';
    } 
    
    public function toXHTML($insertString = null)
    {
        if(!$this->getContent() instanceof Interface_Content_HasScope)
        {
            //only active for contents with scope
            return '';
        }
        $scope = $this->getContent()->getScope();
        if(($scope instanceof Interface_Content_FiniteScope && $scope->isLastPage()) || $scope->getNextPageLink() == null)
        {
            return '';
        }
        $insertString = htmlentities($this->getText(), ENT_QUOTES, CHARSET);
        $str = $this->createNextLink($scope,$insertString);
        return _Formatter_Attribute::toXHTML($str);
    }
    
    protected function createNextLink(Interface_Content_InfiniteScope $scope, $htmlInLink)
    {
        try
        {
            $str = '';
            $link = $scope->getNextPageLink();
            $targetView = $this->getTargetView();
            if(!empty($targetView))
            {
                $targetFrame = $this->getTargetFrame();
                $str = sprintf(
                	"<a href=\"%s\"%s>%s</a>\n"
                    ,$link
                    ,empty($targetFrame) ? '' : ' target="'.htmlentities($targetFrame,ENT_QUOTES,CHARSET).'"'
                    ,$htmlInLink
                );
            }
            else
            {
                $str = 'no target view';
            }
        }
        catch (Exception $e)
        {
            $str =  strval($e);
        }
        return $str;
    }
    
    public function setTargetFrame($frame){}//FIXME
    public function setTargetView($viewName){}//FIXME
}
?>