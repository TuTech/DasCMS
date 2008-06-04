<?php
/**
 * simple xml builder class
 */
class SSimpleXMLWriter extends BSystem 
{
	const Class_Name = 'SSimpleXMLWriter';
	const XML_HEADER = '<?xml version="%s" encoding="%s" ?>';
	
	private $version;
	private $encoding;
	private $stack = array();
	
	public function __construct($encoding = 'UTF-8', $version = '1.0')
	{
		$this->encoding = $encoding;
		$this->version = $version;
	}
	
	public function tag($nodeName, $attributes = array())
	{
		
	}
	
	public function openTag($nodeName, $attributes = array())
	{
		array_push($this->stack, $nodeName);
		
	}
	
	public function closeTag()
	{
		
	}
}
?>