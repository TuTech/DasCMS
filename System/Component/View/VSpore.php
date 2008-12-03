<?php
class VSpore 
    extends 
        BView 
    implements 
        ITemplateSupporter, 
        IGlobalUniqueId 
{
    const GUID = 'org.bambuscms.view.spore';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    //FIXME move QSpore here
    
    private static $spores = array();
    
    private static $functions = array(
        'content' => array('view'),
        'title' => array('view'),
        'pubdate' => array('view'),
        'author' => array('view'),
        'tags' => array('view'),
        'property' => array('view', 'name')
    );
    
    public function __construct()
    {
    }
    
    /**
     * get spore for name
     *
     * @param string $name
     * @return BContent
     */
    private function sporeContent($name)
    {
        if(!QSpore::exists($name) || !QSpore::isActive($name))
        {
            throw new XUndefinedIndexException('spore not found: '.$name);
        }
        if(!array_key_exists($name, self::$spores))
        {
            self::$spores[$name] = new QSpore($name);
        }
        return self::$spores[$name]->getContent();
    }
    
    private function content($spore)
    {
        return $this->sporeContent($spore)->getContent();
    }
    
    private function title($spore)
    {
        return $this->sporeContent($spore)->getTitle();
    }
    
    private function pubdate($spore)
    {
        return $this->sporeContent($spore)->getPubDate();
    }
    
    private function author($spore)
    {
        return $this->sporeContent($spore)->Author;
    }
    
    private function tags($spore)
    {
        return implode(', ',$this->sporeContent($spore)->getTags());
    }
    
    private function property($spore, $propname)
    {
        return $this->sporeContent($spore)->__get($propname);
    }
    
    /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function TemplateProvidedFunctions()
    {
        return self::$functions;
    }
    
    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function TemplateProvidedAttributes()
    {
        return array();
    }

    /**
	 * @param string $function
	 * @return boolean
	 */
	public function TemplateCallable($function)
	{
	    return in_array($function, array_keys(self::$functions));
	}
	
	/**
	 * @param string $function
	 * @param array $namedParameters
	 * @return string in utf-8
	 */
	public function TemplateCall($function, array $namedParameters)
	{
	    if(!$this->TemplateCallable($function))
	    {
	        throw new XTemplateException('called undefined function');
	    }
	    if(!array_key_exists('view', $namedParameters))
	    {
	        throw new XArgumentException('view must be defined');
	    }
	    if($function == 'property' && !array_key_exists('name', $namedParameters))
	    {
	        throw new XArgumentException('property name not defined');
	    }
        return ($function != 'property')
            ? $this->{$function}($namedParameters['view'])
            : $this->{$function}($namedParameters['view'],$namedParameters['name']);
	}
	
	/**
	 * @param string $property
	 * @return string in utf-8
	 */
	public function TemplateGet($property)
	{
	    return '';
	}
}
?>