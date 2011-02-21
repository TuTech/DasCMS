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

	public function  __construct($html = null, $wrapHTML = true) {
		if($html !== null){
			$this->loadHTML($html, $wrapHTML);
		}
	}

	public function loadHTML($html, $wrapHTML = true){
		$head = '';
		$foot = '';
		if($wrapHTML){
			$head = '<?xml version="1.0" encoding="utf-8"?>'.
					'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '.
					'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
					'<html xmlns="http://www.w3.org/1999/xhtml">'.
					'<head>'.
					'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.
					'</head><body>';
			$foot = '</body></html>';
		}

		SErrorAndExceptionHandler::muteErrors();
		$this->domDocument = new DOMDocument('1.0', CHARSET);
		$this->domDocument->encoding = CHARSET;
		$this->domDocument->recover = true;
		$this->domDocument->formatOutput = true;
		$this->domDocument->preserveWhiteSpace = true;
		$this->domDocument->loadHTML($head.$html.$foot);
		SErrorAndExceptionHandler::reportErrors();
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

		$body = str_replace(array("&#13;\r","&#10;\n","&#9;\t"), array("\r","\n","\t"), $body);
		$body = str_replace(array("&#13;","&#10;","&#9;"), array("\r","\n","\t"), $body);

		$this->domDocument = null;
		return $body;
	}
}
?>