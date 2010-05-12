<?php
/**
 * @deprecated use View_Content_* instead
 */
class Formatter_Attribute_View_ScopePageNumber
    extends Formatter_Attribute_Info
    implements Interface_Formatter_Attribute_TextAppendable,
               Interface_Formatter_Attribute_TextPrependable
{
    protected function getFormatterClass()
    {
        return 'ScopePageNumber';
    } 
    
    public function toXHTML($insertString = null)
    {
        if(!$this->getContent() instanceof Interface_Content_HasScope)
        {
            //only active for contents with scope
            return '';
        }
        $scope = $this->getContent()->getScope();
        if(!$scope instanceof Interface_Content_FiniteScope)
        {
            return '';
        }
        return parent::toXHTML($scope->getNumberOfCurrentPage());
    }
}
?>