<?php
/**
 * @author lse
 */
class View_Content_SearchResultCount
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected $prefix = '',
			  $suffix = '';

	public function getPrefix(){
		return $this->prefix;
	}

	public function getSuffix(){
		return $this->suffix;
	}

	public function setPrefix($value){
		$this->prefix = strval($value);
	}

	public function setSuffix($value){
		$this->suffix = strval($value);
	}

	public function toXHTML() {
		$val = '';
		$c = $this->content;
		if($this->shouldDisplay() && $c instanceof CSearch)
		{
			$val = $this->wrapXHTML('ResultCount', $this->prefix . $c->getResultCount() . $this->suffix);
		}
		return $val;
	}

	protected function getPersistentAttributes() {
		return array('prefix', 'suffix');
	}
}
?>