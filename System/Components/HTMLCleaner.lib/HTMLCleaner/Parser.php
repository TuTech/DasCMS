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

	public function  __construct($html = null) {
		if($html !== null){
			$this->loadHTML($html);
		}
	}

	public function loadHTML($html){
		$this->domDocument = new DOMDocument('1.0', CHARSET);
		$this->domDocument->loadHTML($html);
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

	public function run(){
		if(!$this->domDocument){
			return null;
		}
		$root = $this->domDocument->documentElement;
		$this->nodeWalker($root);
		$body = $this->domDocument->saveXML($this->domDocument->getElementsByTagName('body')->item(0));
		$this->domDocument = null;
		return substr($body,6,-7);//strip <body> and </body>
	}
}
?>