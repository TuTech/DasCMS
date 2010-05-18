<?php
/**
 * Description of CSSStyle
 *
 * @author lse
 */
class HTMLCleaner_Cleaner_CSSStyle implements HTMLCleaner_Cleaner {
	const MODE_ALLOW_ONLY = true;
	const MODE_REMOVE = false;


	protected $commands = array();
	protected $mode = self::MODE_REMOVE;

	/**
	 * @param array $commands
	 * @param bool $mode MODE_ALLOW_ONLY or MODE_REMOVE
	 */
	public function  __construct(array $commands, $mode = null) {
		foreach ($commands as $c){
			$this->removeCommand($c);
		}
		if($mode !== null){
			$this->mode = $mode;
		}
	}

	public function removeCommand($command){
		$this->commands[strtolower($command)] = 1;
	}

	public function clean(DOMNode $node) {
		if($node->nodeType == XML_ELEMENT_NODE){
			$atts = $node->attributes;
			if($atts != null){
				$style = $atts->getNamedItem('style');
				if($style != null){
					$style->nodeValue = $this->cleanStyleData($style->nodeValue);
				}
			}
		}
		return true;
	}

	protected function cleanStyleData($styleData){
		$rules = explode(';', $styleData);
		$cleanedRules = array();

		//remove unwanted
		foreach ($rules as $rule){
			if(!empty($rule)){
				list($cmd, $data) = explode(':', $rule);
				$key = strtolower($cmd);
				if(($this->mode == self::MODE_REMOVE && !array_key_exists($key, $this->commands))
						|| ($this->mode == self::MODE_ALLOW_ONLY && array_key_exists($key, $this->commands))
				){
					$cleanedRules[$cmd] = $data;
				}
			}
		}

		//rebuild style string
		$style = '';
		foreach ($cleanedRules as $cmd => $data){
			$style .= sprintf('%s: %s;', $cmd, $data);
		}
		return $style;
	}
}
?>