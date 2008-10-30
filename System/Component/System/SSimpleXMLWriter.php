<?php
/**
 * simple xml builder class
 */
class SSimpleXMLWriter 
    extends 
        BSystem 
{
	const CLASS_NAME = 'SSimpleXMLWriter';
	const XML_HEADER = '<?xml version="%s" encoding="%s" standalone="%s" ?>';
	
	private $version;
	private $encoding;
	private $standAlone;
	private $stack = array();
	private $xml = '';
	private $inputEncoding = 'UTF-8,ISO-8859-1,auto';
	/**
	 * create a new xml constructor
	 * @param string $encoding
	 * @param string $version version of xml 
	 * @param bool $standAlone does not need dtd
	 */
	private $depth = 0;
	public function __construct($encoding = 'UTF-8', $version = '1.0', $standAlone = false)
	{
		$this->nameSpace[0] = null;
		$this->encoding = $encoding;
		$this->version = $version;
		$this->standAlone = empty($standAlone) ? 'no' : 'yes';
	}
	
	public function setInputEncoding($encoding = 'UTF-8')
	{
	    $this->inputEncoding = $encoding;
	}
	
	private function recode($string, $withHTMLEntities = false)
	{
	    if($this->encoding != $this->inputEncoding)
	    {
	        $string = mb_convert_encoding($string, $this->encoding, $this->inputEncoding);
	    }
	    if($withHTMLEntities)
	    {
	        $string = htmlentities($string, ENT_QUOTES, $this->encoding);
	    }
	    return $string;
	}
	
	/**
	 * add new data tag
	 *
	 * @param string $nodeName
	 * @param array $attributes
	 * @param string $value
	 * @param bool $cdata
	 */
	public function tag($nodeName, array $attributes = array(), $value = null, $cdata = false)
	{
		//if (value == null )build <tag /> else <tag></tag>
		if($value === null)
		{
			//use <tag />
			$this->xml .= sprintf(
				"\n%s<%s%s />"
				,str_repeat("\t", $this->depth)
				,$this->recode($nodeName, true)
				,$this->buildAttriburteString($attributes)
			);
		}
		else
		{
			//use <tag>value</tag>
			$this->xml .= sprintf(
				"\n%s<%s%s>%s</%s>"
				,str_repeat("\t", $this->depth)
				,$this->recode($nodeName, true)
				,$this->buildAttriburteString($attributes)
				,($cdata) 
				    ? '<![CDATA['.$this->recode($value).']]>'
					: $this->recode($value, true)
				,$this->recode($nodeName, true)
			);
		}
	}
	
	/**
	 * begin a new node element
	 *
	 * @param string $nodeName
	 * @param array $attributes
	 */
	public function openTag($nodeName, array $attributes = array())
	{
		array_push($this->stack, $nodeName);
		$this->xml .= sprintf(
			"\n%s<%s%s>"
			,str_repeat("\t", $this->depth)
			,$this->recode($nodeName, true)
			,$this->buildAttriburteString($attributes)
		);
		$this->depth++;
	}
	
	/**
	 * close the most recent opened tag
	 * @throws XUndefinedIndexException
	 */
	public function closeTag()
	{
	    $this->depth--;
		if(count($this->stack) == 0)
		{
			throw new XUndefinedIndexException('not open tags on stack');
		}
		$tag = array_pop($this->stack);
		$this->xml .= sprintf("\n%s</%s>",str_repeat("\t", $this->depth),$tag);
	}
	
	/**
	 * close all open tags
	 */
	public function closeAll()
	{
		while (count($this->stack) > 0) 
		{
			$this->closeTag();
		}
	}
	
	/**
	 * build string from attribute array
	 *
	 * @param array $attribues
	 * @return string
	 */
	protected function buildAttriburteString(array $attribues = array())
	{
		$string = "";
		foreach ($attribues as $name => $value) 
		{
			$string = sprintf("%s %s=\"%s\""
				,$string
				,$this->recode($name)
				,$this->recode($value, true)
			);
		}
		return $string;
	}
	
	/**
	 * return xml string
	 */
	public function __toString()
	{
		return sprintf(self::XML_HEADER, $this->version, $this->encoding, $this->standAlone).$this->xml;
	}
}
?>