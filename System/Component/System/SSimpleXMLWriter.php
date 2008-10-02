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
	
	/**
	 * create a new xml constructor
	 * @param string $encoding
	 * @param string $version version of xml 
	 * @param bool $standAlone does not need dtd
	 */
	public function __construct($encoding = 'UTF-8', $version = '1.0', $standAlone = false)
	{
		$this->nameSpace[0] = null;
		$this->encoding = $encoding;
		$this->version = $version;
		$this->standAlone = empty($standAlone) ? 'no' : 'yes';
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
		if($value == null)
		{
			//use <tag />
			$this->xml .= sprintf(
				"<%s%s />"
				,htmlentities($nodeName, ENT_QUOTES, $this->encoding)
				,$this->buildAttriburteString($attributes)
			);
		}
		else
		{
			//use <tag>value</tag>
			$this->xml .= sprintf(
				"<%s%s>%s</%s>"
				,htmlentities($nodeName, ENT_QUOTES, $this->encoding)
				,$this->buildAttriburteString($attributes)
				,($cdata) ? '<![CDATA['.$value.']]>': htmlentities($value, ENT_QUOTES, $this->encoding)
				,htmlentities($nodeName, ENT_QUOTES, $this->encoding)
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
			"<%s%s>"
			,htmlentities($nodeName, ENT_QUOTES, $this->encoding)
			,$this->buildAttriburteString($attributes)
		);
	}
	
	/**
	 * close the most recent opened tag
	 * @throws XUndefinedIndexException
	 */
	public function closeTag()
	{
		if(count($this->stack) == 0)
		{
			throw new XUndefinedIndexException('not open tags on stack');
		}
		$tag = array_pop($this->stack);
		$this->xml .= sprintf("</%s>",$tag);
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
				,htmlentities($name,ENT_QUOTES,$this->encoding)
				,htmlentities($value,ENT_QUOTES,$this->encoding)
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