<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * general atom parsing functions
 * @package Bambus
 * @subpackage _XML
 */
abstract class _XML_Atom extends _XML
{
    /*
	* common attributes
	* 	xml:base
	* 	xml:lang
	* */
    const XMLNS = 'http://www.w3.org/2005/Atom';

    /**
     * list of child nodes
     * @return array
     */
    protected function getElementParsers()
    {
        return array();
    }

    protected function hasChildren($ofType)
    {
        return (is_array($this->{'c__'.$ofType}) && count($this->{'c__'.$ofType}) > 0);
    }

    protected function getFirstChild($ofType)
    {
        if($this->hasChildren($ofType))
        {
            return $this->{'c__'.$ofType}[0];
        }
        else
        {
            return null;
        }
    }

    abstract protected function getAttributeDefinition();

    abstract protected function getElementDefinition();

    protected function isDataNode()
    {
        return false;
    }

    protected function getNodeData()
    {
        return null;
    }

    protected function ignoreAttribute($nodeName, $attributeName, $value)
    {
        return false;
    }

    public function toXML(DOMDocument $doc, $elementName)
    {
        $node = $doc->createElement($elementName);
        if($this->xml_base != null)
        {
            $node->setAttribute('xml:base', $this->xml_base);
        }
        //add attributes
        foreach ($this->getAttributeDefinition() as $att => $mode)
        {
            if($mode == _XML::EXACTLY_ONE || !empty($this->{$att}))
            {
                if(!$this->ignoreAttribute($elementName, $att, $this->{$att}))
                {
                    $node->setAttribute($att, $this->{$att});
                }
            }
        }
        if($this->isDataNode())
        {
            $data = $doc->createTextNode($this->getNodeData());
            $node->appendChild($data);
        }
        else
        {
            //add elements
            foreach ($this->getElementDefinition() as $elm => $mode)
            {
                if(!is_array($this->{'c__'.$elm}))
                {
                    $this->{'c__'.$elm} = array();
                }
                if($mode == _XML::EXACTLY_ONE && count($this->{'c__'.$elm}) == 0)
                {
                    $this->{'c__'.$elm}[] = '';
                }
                if(($mode == _XML::EXACTLY_ONE || $mode == _XML::ONE_OR_MORE) && count($this->{'c__'.$elm}) > 1)
                {
                    $this->{'c__'.$elm} = array($this->{'c__'.$elm}[0]);
                }
        	    foreach ($this->{'c__'.$elm} as $sub)
        	    {
        	        if($sub instanceof Interface_XML_Atom_ToDOMXML)
        	        {
        	            $node->appendChild($sub->toXML($doc, $elm));
        	        }
        	        else
        	        {
        	            $node->appendChild($doc->createElement($elm, strval($sub)));
        	        }
        	    }
            }
        }
        return $node;
    }

    /**
     * get all elements and parse them
     *
     * @param DOMNode $node
     * @param array $withElements
     */
    protected function parseNodeElements(DOMNode $node, array $withElements)
    {
        $this->debug_log('parsing node');
        $this->assertNamespace($node, _XML_Atom::XMLNS);
        $elements = $this->fetchElements($node, $withElements);
        $this->applyElementParser($elements);
    }

    /**
     * map parsed children to object properties
     * array: (element => parser-object)
     *
     * @param array $elements
     */
    protected function applyElementParser(array $elements)
    {
        $elementParsers = $this->getElementParsers();
        $this->debug_log('adding nodes');
        foreach($elementParsers as $element => $parser)
        {
            $this->debug_log('parsing '.$element);
            if($parser == null || !class_exists($parser, true))
            {
                $this->debug_log('skipping');
                continue;
            }
            $this->{'c__'.$element} = array();
            foreach($elements[$element] as $elementNode)
            {
                $this->{'c__'.$element}[] = call_user_func($elementParsers[$element].'::fromNode', $elementNode);
            }
        }
    }

    /**
     * get and parse all attributes
     *
     * @param DOMNode $node
     * @param array $withAttributes
     */
    protected function parseNodeAttributes(DOMNode $node, array $withAttributes)
    {
        $this->debug_log('parsing node');
        $this->assertNamespace($node, _XML_Atom::XMLNS);
        $attributes = $this->fetchAttributes($node, $withAttributes);
        $this->applyAttributeParser($attributes);
    }

    /**
     * map attributes to object properties
     *
     * @param array $attributes
     */
    protected function applyAttributeParser(array $attributes)
    {
        $this->debug_log('adding attributes');
        foreach($attributes as $attribute => $value)
        {
            if(!empty($value))
            {
                $this->{$attribute} = $value;
            }
        }
    }
}
?>