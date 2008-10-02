<?php
class SApplication
    extends 
        BSystem 
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
    
    private $_activeWindowNode = null;
    
    /**
     * @param string $application
     * @throws XFileNotFoundException
     * @throws XInvalidDataException
     */
    public function __construct($application)
    {
        $path = sprintf(
            '%s%s.bap/%s.xml'
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
    
    /**
     * get node for current window
     * @return DOMNode
     */
    private function activeWindowNode()
    {
        if($this->_activeWindowNode == null)
        {
            $requested = RURL::has('window') ? RURL::get('window') : 0;
            $windows = $this->_xpath->query('/application/child::window');
            $i = 0;
            $cwin = 0;
            foreach ($windows as $window) 
            {
                if($requested == $i)
                {
                    $cwin = $i;
                    break;
                }
            	$i++;
            }
            $this->_activeWindowNode = $windows->item($cwin);
        }
        return $this->_activeWindowNode;
    }
    
    /**
     * returns /application/window[active]/$node@$att from app xml
     *
     * @param string $node
     * @param string $att
     * @return string
     */
    private function onActiveWindowGetAttribute($node, $att)
    {
        $c = $this->activeWindowNode()->childNodes;
        $src = null;
        foreach ($c as $child) 
        {
            if($child->nodeName = $node)
            {
                $src = $child->attributes->getNamedItem($att);
            }
        }
        return $src;        
    }
    
    //meta

    /**
     * Name from XML
     *
     * @return string
     */
    public function getName()
    {
        return $this->queryValue('/application/meta/name')->nodeValue;
    }
    
    /**
     * Descripiton from XML
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->queryValue('/application/meta/description')->nodeValue;
    }
    
    /**
     * Icon from XML
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->queryValue('/application/meta/icon')->nodeValue;
    }
    
    /**
     * Vesrion from XML
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->queryValue('/application/meta/version')->nodeValue;
    }
    
    /**
     * GUID from XML
     *
     * @return string
     */
    public function getGUID()
    {
        return $this->queryValue('/application/meta/guid')->nodeValue;
    }
    
    /**
     * Categories from XML
     *
     * @return array
     */
    public function getCategories()
    {   
        $ret = array();
        $categories = $this->_xpath->query('/application/meta/categories/category/attribute::name');
        foreach ($categories as $category) 
        {
            $ret[] = $category->nodeValue;
        }
        return $ret;
        
    }
     //interface
    
    /**
     * Windows from XML array(array(title=> ..., icon=> ...))
     *
     * @return array
     */
    public function getAvailableWindows()
    {
        $ret = array();
        $categories = $this->_xpath->query('/application/child::window');
        foreach ($categories as $category) 
        {
            if($category instanceof DOMElement)
            {
                $ret[] = array(
                    'title' => $category->attributes->getNamedItem('title')->nodeValue,
                    'icon' => $category->attributes->getNamedItem('icon')->nodeValue  
                );
            }
        }
        return $ret;
    }

    /**
     * Title of active window
     *
     * @return string
     */
    public function getWindowTitle()
    {
        return $this->activeWindowNode()->attributes->getNamedItem('title');
    }
    
    /**
     * Icon of active window
     *
     * @return string
     */
    public function getWindowIcon()
    {
        return $this->activeWindowNode()->attributes->getNamedItem('icon');
    }
    
    /**
     * Controller of active window
     *
     * @return string
     */
    public function getController()
    {
        $src = $this->onActiveWindowGetAttribute('controller', 'src');
        if($src == null)
        {
            throw new XInvalidDataException('no controller source');
        }
        return $src;
    }
    
    /**
     * View of active window
     *
     * @return string
     */
    public function getView()
    {
        $src = $this->onActiveWindowGetAttribute('view', 'src');
        if($src == null)
        {
            throw new XInvalidDataException('no view source');
        }
        return $src;
    }
    
    /**
     * View type of active window
     *
     * @return string
     */
    public function getViewType()
    {
        $src = $this->onActiveWindowGetAttribute('view', 'type');
        if($src == null)
        {
            throw new XInvalidDataException('no view type');
        }
        return $src;
    }
    
    //management
    /**
     * get all application names
     *
     * @return array
     */
    public static function listAvailable()
    {
        $apps = array();
        $dirs = DFileSystem::DirsOf(SPath::SYSTEM_APPLICATIONS, '/\.bap$/i');
        foreach ($dirs as $dir) 
        {
        	if(file_exists(SPath::SYSTEM_APPLICATIONS.$dir.'/'.substr($dir,0,-4).'.xml'))
        	{
        	    $apps[] = substr($dir,0,-4);
        	}
        }
        return $apps;
    }

    /**
     * get all meta information for an application
     *
     * @todo optimize fetch method
     * @return string
     */
    public static function readMetadata($fromApplication)
    {
        $me = new SApplication($fromApplication);
        $ret = array(
            'name' => $me->getName(),
            'icon' => $me->getIcon(),
            'version' => $me->getVersion(),
            'guid' => $me->getGUID(),
            'categories' => $me->getCategories()
        );
        return $ret;
    }
}
?>