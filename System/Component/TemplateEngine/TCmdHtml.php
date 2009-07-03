<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-06
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
class TCmdHtml
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $doctype;
    protected $parsed;
    public $data = array();
    
    public function __construct(DOMNode $node)
    {
        foreach ($node->childNodes as $childNode)
        {
            $this->analyze($childNode);
        }
        $atts = $node->attributes;
        $dt = $atts->getNamedItem('doctype');
        if(!$dt)
        {
            return;
        }
        try
        {
            $this->doctype = SResourceString::get('doctypes', $dt->nodeValue);
        }
        catch (Exception $e)
        {
            SNotificationCenter::report('warning', 'unknown_doctype');
        }
    }
    
    public function setUp(array $environment)
    {
        foreach ($this->parsed as $object) 
        {
        	if(is_object($object) && $object instanceof ITemplateCommand)
        	{
    	        $object->setUp($environment);
        	}
        }
    }
    
    public function run(array $environment)
    {
        $out = '<?xml version="1.0" encoding="utf-8"?>'."\n".$this->doctype."\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
        foreach ($this->parsed as $object) 
        {
        	if(is_object($object) && $object instanceof ITemplateCommand)
        	{
    	        $out .= $object->run($environment);
        	}
        	else
        	{
        	    $out .= strval($object);
        	}
        }
        return $out."</html>";
    }
    
    public function tearDown()
    {
        foreach ($this->parsed as $object) 
        {
        	if(is_object($object) && $object instanceof ITemplateCommand)
        	{
    	        $object->tearDown();
        	}
        }
    }

    public function __sleep()
    {
        $this->data = array($this->doctype, $this->parsed);
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->doctype = $this->data[0];
        $this->parsed = $this->data[1];
        $this->data = array();
    }
}
?>