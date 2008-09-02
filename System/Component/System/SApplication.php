<?php
class SApplication // extends BSystem 
{
    /**
     * @var string
     */
    private $_appName;
    /**
     * @var DOMDocument
     */
    private $_dom;
    
    /**
     * @var DOMXPath
     */
    private $_xpath;
    
    /**
     * @param string $application
     * @throws XFileNotFoundException
     * @throws XInvalidDataException
     */
    public function __construct($application)
    {
        $path = sprintf(
            '%s%s/%s.xml'
            ,SPath::SYSTEM_APPLICATIONS
            ,$application
            ,$application
        );
        if(!file_exists($path))
        {
            throw new XFileNotFoundException($application);
        }
        $this->_appName = $application;
        $this->_dom = new DOMDocument();
        if(!@$this->_dom->load($path) || !@$this->_dom->validate())
        {
            throw new XInvalidDataException($path);
        }
        $this->_xpath = new DOMXPath($this->_dom);
    }
    
    
    /**
     * query XPath for single node item
     *
     * @param string $query
     * @return DOMNode
     * @throws XUndefinedIndexException
     */
    private function queryValue($query)
    {
        $entries = $this->_xpath->query($query);
        if($entries->length != 1)
        {
            throw new XUndefinedIndexException('unexpected number of results for XPath query', $entries->length);
        }
        return $entries->item(0);
    }
    
    //meta
    public function getName()
    {
        return $this->queryValue('//application/meta/name')->nodeValue;
    }
    
    public function getDescription()
    {
        return $this->queryValue('//application/meta/description')->nodeValue;
    }
    
    public function getIcon()
    {
        return $this->queryValue('//application/meta/icon')->nodeValue;
    }
    
    public function getVersion()
    {
        return $this->queryValue('//application/meta/version')->nodeValue;
    }
    
    public function getGUID()
    {
        return $this->queryValue('//application/meta/guid')->nodeValue;
    }
    
    public function getCategories()
    {   
        $ret = array();
        $categories = $this->queryValue('//application/meta/categories/category@name');
        foreach ($categories as $category) 
        {
        	$ret[] = $category->nodeValue;
        }
        
    }
     //interface
    
    public function getAvailableWindows()
    {
        
    }

    public function getActiveWindow()
    {
        
    }
    
    
    //management

    public static function listAvailable()
    {
        
    }
    

    
    
    
    public static function readMetadata($fromApplication)
    {
        
    }
}
?>