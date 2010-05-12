<?php
/**
 * @deprecated use View_Content_* instead
 */
class Formatter_Attribute_View_ScopePrevious
    extends Formatter_Attribute_Link
{
    public function getLinkAlias()
    {
        return $this->getContent()->getAlias();
    }
    
    protected function getFormatterClass()
    {
        return 'ScopePrevious';
    } 
    
    public function toXHTML($insertString = null)
    {
        if(!$this->getContent() instanceof Interface_Content_HasScope)
        {
            //only active for contents with scope
            return '';
        }
        $scope = $this->getContent()->getScope();
        if(($scope instanceof Interface_Content_FiniteScope && $scope->isFirstPage()) || $scope->getPreviousPageLink() == null)
        {
            return '';
        }
        $insertString = htmlentities($this->getText(), ENT_QUOTES, CHARSET);
        $str = $this->createPreviousLink($scope,$insertString);
        return _Formatter_Attribute::toXHTML($str);
    }
    
    protected function createPreviousLink(Interface_Content_InfiniteScope $scope, $htmlInLink)
    {
        try
        {
            $str = '';
            $link = $scope->getPreviousPageLink();
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