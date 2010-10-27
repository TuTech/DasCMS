<?php
/**
 * Description of Group
 *
 * @author lse
 */
class View_Content_Form
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	const PLAIN = 0;
	const ENCODED = 1;
	
	const PLAIN_VALUE = 'text/plain';
	const ENCODED_VALUE = "application/x-www-form-urlencoded";

	const METHOD_GET = 'get';
	const METHOD_POST = 'post';

	protected $method = self::METHOD_GET;
	protected $subViews;
	protected $encoding = self::PLAIN;
	protected $action = null;
	protected $name = null;

	/**
	 * is form encoding enabled
	 * @return bool
	 */
	public function isEncoded(){
		return !!$this->encoding;
	}

	/**
	 * en-/disable form encoding for fileuploads
	 * @param bool $on 
	 */
	public function setEncoded($on){
		$this->encoding = $on ? self::ENCODED : self::PLAIN;
	}

	/**
	 * get or post
	 * @return string
	 */
	public function getMethod(){
		return $this->method;
	}

	/**
	 * set method to get or post
	 * @param string $value
	 */
	public function setMethod($value){
		$value = strtolower($value);
		if($value == self::METHOD_GET 
				|| $value == self::METHOD_POST)
		{
			$this->method = $value;
		}
	}

	/**
	 * set target content
	 * @param Interface_Content $content
	 */
	public function acceptContent(Interface_Content $content) {
		parent::acceptContent($content);
		if(is_array($this->subViews)){
			foreach ($this->subViews as $view){
				$view->acceptContent($content);
			}
		}
	}

	/**
	 * render form
	 * @return string
	 */
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			if($this->method == 'get'){
				//preserve all input data from current url
				foreach (RURL::data(CHARSET) as $k => $v){
					$val .= sprintf(
						'<input type="hidden" name="%s" value="%s" />',
						String::htmlEncode($k),
						String::htmlEncode($v)
					);
				}
			}
			if(is_array($this->subViews)){
				$cssTpl = "Group_item Group_item_%d";
				for($i = 0; $i < count($this->subViews); $i++){
					$this->subViews[$i]->addCustomCSSClass(sprintf($cssTpl, $i+1));
					$val .=  $this->subViews[$i]->toXHTML();
				}
			}
			$val = $this->wrapXHTML('', $val);
		}
		return $val;
	}

	/**
	 * add sub view
	 * @param _View_Content_Base $subView
	 */
	public function addSubView(_View_Content_Base $subView){
		if($this->subViews == null){
			$this->subViews = array();
		}
		$this->subViews[] = $subView;
	}

	/**
	 * persistent attributes
	 * @return array
	 */
	protected function getPersistentAttributes() {
		return array('subViews', 'encoding', 'method');
	}

	/**
	 * make this a "form"-tag
	 * @return string
	 */
	protected function getWrapperTag() {
		return 'form';
	}

	/**
	 * return form attributes
	 * @return array
	 */
	protected function getWrapperAttributes() {
		if($this->action == null){
			//the complete current url with all temp fields
			$action = SLink::buildURL(RURL::data(CHARSET));
		}
		elseif(is_array($this->action)){
			//build url from config and input
			$data = array();
			foreach ($this->action as $k => $v){
				if(empty($v)){
					$data[$k] = RURL::get($k, CHARSET);
				}
				else{
					$data[$k] = $v;
				}
			}
			$action = SLink::buildURL($data);
		}
		else{
			$action = $this->action;
		}

		//data will be html encoded by calling function
		$data = array(
			'method' => $this->method,
		  	'action' => $action,
		 	'encoding' => $this->encoding == self::PLAIN ? self::PLAIN_VALUE : self::ENCODED_VALUE,
		);
		if($this->name){
			$data['name'] = $this->name;
		}
		return $data;
	}
}
?>

