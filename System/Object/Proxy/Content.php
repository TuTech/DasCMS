<?php
class Proxy_Content 
    extends _Proxy
    implements Interface_Content
{
	
	protected static $contents = array();
	
	/**
	 * @param BContent $content
	 * @return Proxy_Content
	 */
	public static function create(BContent $content)
	{
	    $prevIds = array($content->getId());
		do{
		    $failed = false;
		    $e = new EWillAccessContentEvent($content, $content);
		    if($e->isCanceled())
		    {
		        $content = new CError(403);
		    }
		    elseif($e->hasContentBeenSubstituted())
		    {
		        $content = $e->Content;
		        $id = $content->getId();
		        $failed = true;
		        
		        //loop prevention
		        if(in_array($id, $prevIds))
		        {
		            $content = new CError(500);
		            $failed = false;
		            break;
		        }
		        $prevIds[] = $id;
		        
		        //already resolved substitude?
		        if(isset(self::$contents[$content->getId()]))
		        {
		            //cache looped
		            foreach ($prevIds as $id)
		            {
		                self::$contents[$id] = self::$contents[$content->getId()];
		            }
		            return self::$contents[$content->getId()];
		        }
		    }
		}while ($failed);
	    
		$id = $content->getId();
		if(!isset(self::$contents[$id]))
		{
			self::$contents[$id] = new Proxy_Content($content);
		}
		return self::$contents[$id];
	}
	
	/**
	 * @var BContent
	 */
	protected $content;
	
	protected function __construct(BContent $content)
	{
		$this->content = $content;
		$e = new EContentAccessEvent($this, $content);
		if($e->isCanceled())
		{
		    $this->content = new CError(403);
		}
	}
	
	/**
	 * function mapper
	 * @param string $function
	 * @return mixed
	 */
	public function __call($function, $params = array())
	{
		if((substr($function,0,3) == 'get'
				|| substr($function,0,4) == 'send')
			&& method_exists ($this->content, $function))
		{
			return $this->content->{$function}();
		}
		return null;
	}
	
	/**
	 * property check
	 * @param string $var
	 * @return bool
	 */
	public function __isset($var)
	{
		return method_exists ($this->content, 'get'.ucfirst($var));
	}	
	
	/**
	 * property mapper 
	 * @param string $var
	 * @return mixed
	 */
	public function __get($var)
	{
		return $this->__call('get'.ucfirst($var));
	}
	
	/**
	 * @param string $interface
	 * @return bool
	 */
	public function implementsInterface($interface)
	{
		return $this->content instanceof $interface;
	}
	
	/**
	 * @param string $composite
	 * @return bool
	 */
	public function implementsComposite($composite)
	{
		return $this->content->hasComposite($composite);
	}
	
	public function attachComposite(Interface_Composites_Attachable $composite)
	{
	    $this->content->attachComposite($composite);
	}
	
	//interface Interface_Content
	
    public function setParentView(VSpore $pv){
        $this->content->setParentView($pv);
    }
    public function getParentView(){
        return $this->content->getParentView();
    }
    
    public function getId(){
        return $this->content->getId();
    }
   
    public function getGUID(){
        return $this->content->getGUID();
    }
    
    public function getIcon(){
        return $this->content->getIcon();
    }

    public function getPreviewImage(){
        return $this->content->getPreviewImage();
    }
    
    public function getTitle(){
        return $this->content->getTitle();
    }
    
    public function getSubTitle(){
        return $this->content->getSubTitle();
    }
    
    public function getMimeType(){
        return $this->content->getMimeType();
    }
    
    public function getTags(){
        return $this->content->getTags();
    }
    
    public function getAlias(){
        return $this->content->getAlias();
    }
    
    public function getSize(){
        return $this->content->getSize();
    }
    
    public function getPubDate(){
        return $this->content->getPubDate();
    }
    
    public function getSource(){
        return $this->content->getSource();
    }
    
    public function getContent(){
        return $this->content->getContent();
    }
    
    public function getDescription(){
        return $this->content->getDescription();
    }
    
    public function getText(){
        return $this->content->getText();
    } 
}
?>