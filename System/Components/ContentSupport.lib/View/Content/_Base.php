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
			  $linkCaption = null,
			  $elementId = null;
	protected $linkTargetViewObject = null;
	
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
		try{
			$val = '';
			if($this instanceof Interface_View_DisplayXHTML){
				$val = $this->toXHTML();
			}
		}
		catch (Exception $e){
			return '<!-- Error: '.strval($e) .' -->';
		}
		return $val;
	}

	/**
	 * make html div wrap
	 * @param string $class
	 * @param string $val
	 * @return string
	 */
	protected function wrapXHTML($class, $value, $autoLink = true){
		//add custom css class
		if($this->customCSSClass){
			$class .= ' '.String::htmlEncode($this->customCSSClass);
		}

		//build a link wrapper if target view is set
		if($autoLink && $this->linkTargetView){
			if($this->linkTargetViewObject == null){
				if(Controller_View_Content::exists($this->linkTargetView)){
					$this->linkTargetViewObject = Controller_View_Content::byName($this->linkTargetView);
				}
				else{
					$this->linkTargetViewObject = false;
				}
			}
			if($this->linkTargetViewObject !== false){
				$value = sprintf(
						"<a href=\"%s\"%s>%s</a>",
						$this->linkTargetViewObject->linkTo($this->content->getAlias()),
						($this->linkTragetFrame ? ' '.String::htmlEncode($this->linkTragetFrame) : ''),
						$value
					);
			}
		}

		//get tag for this element
		$tag = $this->getWrapperTag();

		//get additional attributes
		$atts = $this->getWrapperAttributes();
		$atts['class'] =  $class;
		if($this->elementId !== null){
			$atts['id'] = $this->elementId;
		}
		$attData = array();
		foreach ($atts as $aName => $aValue){
			$attData[] = sprintf('%s="%s"', String::htmlEncode($aName), String::htmlEncode($aValue));
		}
		return sprintf("<%s %s>%s</%s>",$tag, implode(' ',$attData), $value, $tag);
	}

	protected function getWrapperTag(){
		return 'div';
	}

	protected function getWrapperAttributes(){
		return array();
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
			'customCSSClass' => $this->customCSSClass,
			'linkTragetFrame' => $this->linkTragetFrame,
			'linkTargetView' => $this->linkTargetView,
			'linkCaption' => $this->linkCaption,
			'delegate' => $this->delegate,
			'elementId' => $this->elementId
		);
		foreach ($baseAttributes as $baseAttribute => $value){
			if($value !== null){
				$this->_sleepStorage[$baseAttribute] = $value;
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
	 * getter for elementId
	 * @return string
	 */
	public function getElementID(){
		return $this->elementId;
	}

	/**
	 * setter for additional elementId
	 * @param string $value
	 */
	public function setElementID($value){
		$this->elementId = strval($value);
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

	/**
	 * setter for additional custom css class
	 * @param string $value
	 */
	public function addCustomCSSClass($value){
		$this->customCSSClass .= (empty($this->customCSSClass) ? '' : ' ').strval($value);
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