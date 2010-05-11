<?php
/**
 * Description of ViewDelegatesGroup
 *
 * @author lse
 */
class Controller_DelegateGroup {
	protected $subdelegates = array();

	public function  __call($name,  $arguments) {
		$res = true;
		foreach ($this->subdelegates as $del){
			if(is_callable(array($del, $name)))
			$res = $res && call_user_func_array(array($del, $name), $arguments);
		}
		return $res;
	}

	public function addDelegate($delegate){
		if(is_object($delegate)){
			$this->subdelegates[] = $delegate;
		}
	}

	public function removeDelegate($delegate){
		$new = array();
		foreach ($this->subdelegates as $del){
			if($delegate !== $del){
				$new[] = $del;
			}
		}
		$this->subdelegates = $new;
	}

	public function  __sleep() {
		return array('subdelegates');
	}
}
?>