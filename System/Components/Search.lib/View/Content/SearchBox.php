<?php
/**
 * @author lse
 */
class View_Content_SearchBox
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected static $qsSent = array();
	protected $targetView = null;
	protected $parameterName = 'q';
	protected $placeholder = '';

	public function toXHTML() {
		$val = '';
		$targetView = $this->getTargetView();
		$par = '_'.$this->parameterName;
		if($this->shouldDisplay() 
				&& $this->content instanceof Interface_Search_AcceptsSubQueries
				&& $this->content->isSubQueryingAllowed()
				&& Controller_View_Content::exists($targetView))
		{
			$CVC = Controller_View_Content::byName($targetView);
			$val = $this->wrapXHTML('SearchBox',sprintf(
					'<input type="search" name="%s" value="%s" %s%s/>',
					$CVC->buildParameterName($par),
					String::htmlEncode($CVC->GetParameter($this->parameterName, CHARSET)),
					(empty ($this->elementId)) ? '' : ' id="'.String::htmlEncode($this->elementId.'-input').'"',
					(empty ($this->placeholder)) ? '' : ' placeholder="'.String::htmlEncode($this->placeholder).'"'
			));
		}
		return $val;
	}

	public function acceptContent(Interface_Content $content) {
		parent::acceptContent($content);

		//add to query string
		$targetView = $this->getTargetView();
		if(!isset(self::$qsSent[$this->parameterName])
				&& $content instanceof Interface_Search_AcceptsSubQueries
				&& $content->isSubQueryingAllowed()
				&& Controller_View_Content::exists($targetView))
		{
			$content->addSubQuery(Controller_View_Content::byName($targetView)->GetParameter($this->parameterName, CHARSET));
			self::$qsSent[$this->parameterName] = 1;
		}
	}

	protected function getPersistentAttributes() {
		return array(
			'parameterName',
			'targetView',
			'placeholder'
		);
	}

	public function getParameterName(){
		return $this->parameterName;
	}

	public function setParameterName($value){
		if(!ctype_alpha($value)){
			return;
		}
		$this->parameterName = $value;
	}

	public function getTargetView() {
		return $this->targetView;
	}

	public function setTargetView($value) {
		$this->targetView = strval($value);
	}

	public function getPlaceholder() {
		return $this->placeholder;
	}

	public function setPlaceholder($value) {
		$this->placeholder = strval($value);
	}
}
?>