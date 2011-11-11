<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_Button
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	protected $type = 'submit';
	protected $value = 'OK';
	protected $name = '';

	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			$val = $this->wrapXHTML('Button', sprintf(
					'<input type="%s" value="%s" name="%s" />',
					$this->type,
					String::htmlEncode($this->value),
					String::htmlEncode($this->name)
				));
		}
		return $val;
	}

	public function getType(){
		return $this->type;
	}
	public function setType($value){
		$this->type = (strtolower($value) == 'submit') ? 'submit' : 'reset';
	}

	public function getName(){
		return $this->name;
	}
	public function setName($value){
		$this->name = $value;
	}

	public function getValue(){
		return $this->value;
	}
	public function setValue($value){
		$this->value = $value;
	}

	protected function getPersistentAttributes() {
		return array('name', 'type', 'value');
	}
}
?>