<?php
/**
 * universal atom parsing functions 
 */
abstract class _XML_Atom extends _XML 
{
    /*  
	* common attributes
	* 	xml:base
	* 	xml:lang
	* */
    const NAMESPACE = 'http://www.w3.org/2005/Atom';
    
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
    
    protected function getChildMap($forType)
    {
        //FIXME
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
        $this->assertNamespace($node, _XML_Atom::NAMESPACE);
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
        $this->assertNamespace($node, _XML_Atom::NAMESPACE);
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