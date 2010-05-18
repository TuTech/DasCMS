<?php
/**
 * Description of Parser
 *
 * @author lse
 */
class HTMLCleaner_Parser {
	/**
	 * @var DOMDocument
	 */
	protected $domDocument;
	protected $cleaners = array();
	protected static $selfClosingTags = array(
		"br", "hr", "input", "frame", "img", "area", "link", "col", "base", "basefont", "param"
	);

	public function  __construct($html = null) {
		if($html !== null){
			$this->loadHTML($html);
		}
	}

	public function loadHTML($html){
		$this->domDocument = new DOMDocument('1.0', CHARSET);
		$this->domDocument->loadHTML($html);
		$this->domDocument->encoding = CHARSET;
		$this->domDocument->preserveWhiteSpace = false;
	}

	public function addCleaner(HTMLCleaner_Cleaner $cleaner){
		$this->cleaners[] = $cleaner;
	}

	protected function nodeWalker(DOMNode $node){
		if(!$node->nodeType == XML_ELEMENT_NODE || !$node->childNodes){
			return ;
		}
		//child elements to remove
		$purge = array();

		//walk child nodes
		foreach ($node->childNodes as $child){
			$allow = true;

			//go through the cleaners
			foreach ($this->cleaners as $cleaner){
				$allow = $allow && $cleaner->clean($child);
			}

			//set child to be removed
			if(!$allow){
				$purge[] = $child;
			}
		}

		//remove unwanted childNodes
		foreach ($purge as $child){
			$node->removeChild($child);
		}

		//now walk the remaining subnodes
		foreach ($node->childNodes as $child){
			$this->nodeWalker($child);
		}
	}

	protected static function convCallback($match){
		$tag = in_array($match[1], self::$selfClosingTags)
				? '<%s%s />'
				: '<%s%s></%s>';
		return sprintf($tag, $match[1], $match[2], $match[1]);
	}

	public function run(){
		if(!$this->domDocument){
			return null;
		}
		$root = $this->domDocument->documentElement;
		$this->nodeWalker($root);
		$body = $this->domDocument->saveXML();
		$body = substr($body, strpos($body, '<body>')+6);
		$body = substr($body, 0, strripos($body, '</body>'));
		$body = preg_replace_callback('#<(\w+)([^>]*)\s*/>#s', 'HTMLCleaner_Parser::convCallback' , $body);
		$body = html_entity_decode($body, ENT_QUOTES, CHARSET);

		$this->domDocument = null;
		return $body;
	}
}
?>