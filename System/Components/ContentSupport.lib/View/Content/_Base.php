<?php
/**
 * Description of _View_Content_Base
 *
 * @author lse
 */
class _View_Content_Base
	implements Interface_AcceptsContent{
	/**
	 * internal use only
	 * @var array
	 */
	public $_sleepStorage = null;

	/**
	 * @var object
	 */
	protected $delegate;

	//todo custom css class
	//linking for all

	/**
	 * @var Interface_Content
	 */
	protected $content;

	/**
	 * linking
	 * not saved by default add to "getPersistentAttributes()" if needed
	 * @var string
	 */
	protected $linkTragetFrame = null,
			  $linkTargetView = null,
			  $linkCaption = null;

	/**
	 * custom css class
	 * @var string
	 */
	protected $customCSSClass = null;

	/**
	 * set content for formatting
	 * @param Interface_Content $content
	 */
	public function acceptContent(Interface_Content $content) {
		$this->content = $content;
	}

	/**
	 * set delegate handler
	 * @param object $delegate
	 */
	public function setDelegate($delegate){
		$this->delegate = $delegate;
	}

	/**
	 * call default display delegate
	 * @return bool
	 */
	protected function shouldDisplay(){
		return $this->callDelegate('contentViewShouldDisplay', array($this, $this->content), true);
	}

	/**
	 * inform delegate
	 * @param string $call
	 * @param array $params
	 * @param mixed $defaultReturn
	 * @return mixed
	 */
	protected function callDelegate($call, array $params, $defaultReturn = null) {
		if ($this->delegate !== null
				&& is_object($this->delegate)
				&& is_callable(array($this->delegate, $call))){
			return call_user_func_array(array($this->delegate, $call), $params);
		}
		return $defaultReturn;
	}

	/**
	 * provide string value based on toXHTML()
	 * @return string
	 */
	public function  __toString() {
		$val = '';
		if($this instanceof Interface_View_DisplayXHTML){
			$val = $this->{"toXHTML"}();
		}
		return $val;
	}

	/**
	 * make html div wrap
	 * @param string $class
	 * @param string $val
	 * @return string
	 */
	protected function wrapXHTML($class, $val, $autoLink = true){
		//TODO do the linking magic
		return sprintf("\n<div class=\"%s\">%s</div>", $class, $val);
	}

	/**
	 * report attributes to be saved on sleep
	 * @return array
	 */
	protected function getPersistentAttributes(){
		return array();
	}

	/**
	 * save persistent data
	 * @return array
	 */
	public function  __sleep() {
		$this->_sleepStorage = array();
		$baseAttributes = array(
			'customCSSClass',
			'linkTragetFrame',
			'linkTargetView',
			'linkCaption'
		);
		foreach ($baseAttributes as $baseAttribute){
			if($this->{$baseAttribute} !== null){
				$this->_sleepStorage[$baseAttribute] = $this->{$baseAttribute};
			}
		}
		foreach ($this->getPersistentAttributes() as $key) {
			if($this->{$key} !== null){
				$this->_sleepStorage[$key] = $this->{$key};
			}
		}
		return array('_sleepStorage');
	}

	/**
	 * restore persistent data
	 */
	public function  __wakeup() {
		foreach ($this->_sleepStorage as $key => $value) {
			$this->{$key} = $value;
		}
		$this->_sleepStorage = null;
	}

	/**
	 * getter for additional custom css class
	 * @return string
	 */
	public function getCustomCSSClass(){
		return $this->customCSSClass;
	}

	/**
	 * setter for additional custom css class
	 * @param string $value
	 */
	public function setCustomCSSClass($value){
		$this->customCSSClass = strval($value);
	}

	//optional linking stuff

	/**
	 * target frame getter
	 * @return string
	 */
	protected function getLinkTargetFrame(){
		return $this->linkTragetFrame;
	}

	/**
	 * target frame setter
	 * @param string $value
	 */
	protected function setLinkTargetFrame($value){
		$this->linkTragetFrame = strval($value);
	}

	/**
	 * target view getter
	 * @return string
	 */
	protected function getLinkTargetView(){
		return $this->linkTargetView;
	}

	/**
	 * target view setter
	 * @param string $value
	 */
	protected function setLinkTargetView($value){
		$this->linkTargetView = strval($value);
	}

	/**
	 * custom link caption getter
	 * @return string
	 */
	protected function getLinkCaption(){
		return $this->linkCaption;
	}

	/**
	 * custom link caption setter
	 * @param string $value
	 */
	protected function setLinkCaption($value){
		$this->linkCaption = strval($value);
	}
}
?>