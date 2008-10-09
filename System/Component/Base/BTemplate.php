<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 09.10.2008
 * @license GNU General Public License 3
 */
abstract class BTemplate extends BObject
{
    const CONTENT = 'C';
    const SYSTEM = 'S';
    
    protected $parsed = array();
    protected $parsedLast = 0;
    
    protected function analyze(DOMNode $node)
    {
        switch ($node->nodeType) 
        {
        	case XML_ELEMENT_NODE: //commands live here
        	    if($node->namespaceURI == 'http://www.bambuscms.org/2008/TemplateXML')
                {
                    try
                    {
                        $command = 'TCmd'.ucfirst(strtolower($node->localName));
                        if(!class_exists($command, true))
                        {
                            throw new XTemplateException('unknown element '.$node->localName);
                        }
                        $executor = new $command($node);
                        if(!$executor instanceof BTemplate)
                        {
                            throw new XTemplateException('unsupported element '.$node->localName);
                        }
                    }
                    catch(Exception $e)
                    {
                        $executor = sprintf(
                            '[%s:%d] %s in %s at %d'
							,get_class($e)
                            ,$e->getCode()
                            ,$e->getMessage()
                            ,$e->getFile()
                            ,$e->getLine()
                        );
                    }
                    $this->appendData($executor);
                }
                else //nothing commands here
                {
                    //open tag
                    $this->htmlTagBegin($node);
                    //continue with children
                    foreach ($node->childNodes as $child) 
                    {
                    	$this->analyze($child);
                    }
                    //and close
                    $this->htmlTagEnd($node);
                }
        		break;
        	case XML_TEXT_NODE:
        	case XML_PI_NODE:
        	case XML_NOTATION_NODE:
        	case XML_ERROR_NONE:
        	case XML_ENTITY_REF_NODE:
        	case XML_ENTITY_NODE:
        	case XML_ENTITY_DECL_NODE:
        	case XML_ELEMENT_DECL_NODE:
        	case XML_COMMENT_NODE:
        	case XML_CDATA_SECTION_NODE:
        	    $this->appendData(strval($node->nodeValue));
        	    break;
        	default:
        	break;
        }
    }
    
    protected function htmlTagBegin(DOMNode $node)
    {
        $attStr = '';
        foreach ($node->attributes as $name => $value) 
        {
        	$attStr .= sprintf(' %s="%s"', $name, htmlentities($value, ENT_QUOTES, 'utf-8'));
        }
        $this->appendData(sprintf('<%s%s>', $node->nodeName, $attStr));
    }
    
    protected function htmlTagEnd(DOMNode $node)
    {
        $this->appendData(sprintf('</%s>', $node->nodeName));
    }
    
    protected function appendData($data)
    {
        if(is_object($data))
        {
            $this->parsed[] = $data;
            $this->parsedLast = 'obj';
        }
        else
        {
            if($this->parsedLast == 'str')
            {
                $this->parsed[count($this->parsed)-1] .= $data;
            }
            else
            {
                $this->parsed[] = $data;
            }
            $this->parsedLast = 'str';
        }
    }
}
?>