<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _
 */
abstract class _XML 
{
    //child modes
    const ONE_OR_MORE = '+';
    const EXACTLY_ONE = '1';
    const NONE_OR_ONE = '?';
    const NONE_OR_MORE = '*';
    
    protected $xml_base = null;
    
    /**
     * create a DOMDocument from a file
     *
     * @param string $file
     * @return DOMDocument
     */
    protected static function loadFile($file)
    {
        //load file, read xml, throw ex if invalid
        $dom = new DOMDocument('1.0');
        if(!file_exists($file))
        {
            throw new Exception('xml file does not exist', 1);
        }
        if(!is_readable($file))
        {
            throw new Exception('xml file is not readable', 1);
        }
        if(!@$dom->load($file))
        {
            throw new Exception('could not load xml', 1);
        }
        return $dom;
    }
    
    /**
     * create a DOMDocument from a string
     *
     * @param string $xml
     * @return DOMDocument
     */
    protected static function loadString($xml)
    {
        //load file, read xml, throw ex if invalid
        $dom = new DOMDocument('1.0');
        if(!@$dom->loadXML($xml))
        {
            throw new Exception('could not load xml', 1);
        }    
        return $dom;
    }
    
    /**
     * given a DOMNode and an array (elementName => childMode) 
     * this funktion will return an array (elementName => array(elements))
     * 
     * @param DOMNode $node
     * @param array $elements
     * @return array
     */
    protected function fetchElements(DOMNode $node, array $elements)
    {
        $result = array();
        foreach ($elements as $element => $childMode) 
        {
        	$result[$element] = $this->getNamedChildElements($node, $element, $childMode);
        }
        return $result;
    }
        
    /**
     * get child nodes with the name $elementName from DOMNode $node
     *
     * @param DOMNode $node
     * @param string $elementName
     * @param string $childMode a _XML:: const
     * @return array
     */
    protected function getNamedChildElements(DOMNode $node, $elementName, $childMode = _XML::NONE_OR_MORE)
    {
        $children = array();
        if($node->hasChildNodes())
        {
            foreach($node->childNodes as $child)
            {
                if($child->localName == $elementName)
                {
                    $children[] = $child;
                }
            }
        }
        $this->assertChildMode($childMode, count($children));
        return $children;
    }
    
    /**
     * given a node and an array (attributeName => childMode) 
     * this funktion will return an array (attributeName => array(elements))
     * 
     * @param DOMNode $node
     * @param array $attributes
     * @return array
     */
    protected function fetchAttributes(DOMNode $node, array $attributes)
    {
        $result = array();
        foreach ($attributes as $attribute => $childMode) 
        {
        	$result[$attribute] = $this->getNamedChildAttributes($node, $attribute, $childMode);
        }
        return $result;
    }
        
    /**
     * get attribute with the name $attributeName from the DOMNode $node
     *
     * @param DOMNode $node
     * @param string $attributeName
     * @param string $childMode
     * @return string|null
     */
    protected function getNamedChildAttributes(DOMNode $node, $attributeName, $childMode = _XML::NONE_OR_MORE)
    {
        $attList = $node->attributes;
        $att = $attList->getNamedItem($attributeName);
        if(!$att)
        {
            $this->assertChildMode($childMode, 0);
            return null;
        }
        else
        {
            $this->assertChildMode($childMode, 1);
            return $att->nodeValue;
        }
    }
    
    /**
     * throws exception if the namespace of $node does not match $namespace
     *
     * @param DOMNode $node
     * @param sring $namespace
     * @throws Exception
     */
    protected function assertNamespace(DOMNode $node, $namespace)
    {
        if($node->namespaceURI != $namespace)
        {
            throw new Exception(sprintf('Invalid namespace for node. %s has %s but should be %s', $node->localName, $node->namespaceURI, $namespace),1);
        }
    }
        
    /**
     * throws exception if the $childCount conflicts with $childMode
     *
     * @param string $childMode
     * @param integer $childCount
     */
    protected function assertChildMode($childMode, $childCount)
    {
        $msg = 'Invalid child failed. %d children found but %s expected';
        if(!in_array($childMode, array(_XML::EXACTLY_ONE, _XML::NONE_OR_MORE, _XML::NONE_OR_ONE, _XML::ONE_OR_MORE)))
        {
            throw new Exception('Invalid child mode');
        }
        if($childMode == _XML::EXACTLY_ONE && $childCount != 1)
        {
            throw new Exception(sprintf($msg, $childCount, '1'),1);
        }
        elseif($childMode == _XML::ONE_OR_MORE && $childCount == 0)
        {
            throw new Exception(sprintf($msg, $childCount, '1 or more'),1);
        }
        elseif($childMode == _XML::NONE_OR_ONE && $childCount > 1)
        {
            throw new Exception(sprintf($msg, $childCount, 'none or 1'),1);
        }
    }
}

?>