<?php
class Controller_Content 
{
    //IShareable	
    
    /**
     * @var Controller_Content
     */
	private static $sharedInstance = null;
	//alias => [id, content proxy]
	private $accessedContents = array();
	/**
     * @return Controller_Content
     */
	public static function getSharedInstance()
	{
		if(self::$sharedInstance == null)
		{
			self::$sharedInstance = new Controller_Content();
		}
		return self::$sharedInstance;
	}
	
	//end IShareable	
	
	/**
	 * @param $alias
	 * @param $ifIsType
	 * @return BContent
	 */
    public function openContent($alias, $ifIsType = null)
    {
	    if(empty($alias))
	    {
	        throw new XUndefinedException('no alias');
	    }
        $class = QBContent::getClass($alias);
        if(class_exists($class, true) && ($ifIsType == null || $class == $ifIsType))
        {
            return new $class($alias);
        }
        else
        {
            throw new XInvalidDataException($class.' not found');
        }
    }
    
    /**
     * always returns a content
     * no exceptions
     * @param $alias
     * @return BContent
     */
    public function tryOpenContent($alias, $ifIsType = null)
    {
	    try
	    {
	        return $this->openContent($alias, $ifIsType);
	    }
	    catch(Exception $e)
	    {
            return new CError(404);
	    }
    }
    
    /**
     * @param $alias
     * @param $opener
     * @param $failIfReplaced
     * @return Proxy_Content
     */
    public function accessContent($alias, BObject $opener, $failIfReplaced = false)
    {
        //accessed contents are only opened once, except an outdated alias is used
        if(isset($this->accessedContents[$alias]))
        {
            list($origId, $proxy) = $this->accessedContents[$alias];
        }
        else
        {
            $content = $this->openContent($alias);
            $origAlias = $content->getAlias();
            $origGuid = $content->getGUID();
            $origId = $content->getId();
            $proxy = Proxy_Content::create($content);
            $this->accessedContents[$origGuid] = array($origId, $proxy);
            $this->accessedContents[$origAlias] = &$this->accessedContents[$origGuid];
            if($proxy->getId() != $origId)
            {
                $this->accessedContents[$proxy->getAlias()] = &$this->accessedContents[$origGuid];
                $this->accessedContents[$proxy->getGUID()] = &$this->accessedContents[$origGuid];
            }
        }
        if($proxy->getId() != $origId && $failIfReplaced)
        {
            throw new XInvalidDataException('content replaced but exact open requested');
        }
        return $proxy;
    }
    
    /*public function createContent($title, $class)
    {
        
    }*/
    
    public function deleteContent($alias)
    {
        try
	    {
	        $succ = QBContent::deleteContent($alias);
	    }
	    catch (XDatabaseException $d)
	    {
	        SNotificationCenter::report('warning', 'element_is_used_by_the_system_and_cannot_be_deleted');
	        $succ = false;
	    }
	    catch (Exception $e)
	    {
	        SNotificationCenter::report('warning', 'delete_failed');
	        $succ = false;
	    }
	    return $succ;
    }
    
    public function contentExists($alias)
    {
        $res = QBContent::exists($alias);
	    $c = $res->getRowCount();
	    $res->free();
	    return $c == 1;
    }
    
    public function contentIndex($class, $titleOnly = false)
    {
		try
		{
		    $res = QBContent::getBasicInformationForClass($class);
			$index = array();
			while ($arr = $res->fetch())
			{
			    list($title, $pubdate, $alias, $type, $id) = $arr; 
				$index[$alias] = $titleOnly ? $title : array($title, $pubdate, $type, $id);
			}
			$res->free();
		}
		catch (Exception $e)
		{
			$index = array();
		}
		return $index;
    }
    
	/**
	 * guid => title
	 * @return array
	 */
	public function contentGUIDIndex($ofClass)
	{
	    $index = array();
	    $res = QBContent::getGUIDIndexForClass($ofClass);
	    while ($row = $res->fetch())
	    {
	        $index[$row[0]] = $row[1];
	    }
	    return $index;
	}
	
	public function getContentInformationBulk(array $aliases)
	{
	    $res = QBContent::getPrimaryAliases($aliases);
	    $map = array();
	    $revmap = array();
	    $infos = array();
	    while ($erg = $res->fetch())
		{
		    list($reqest, $primary) = $erg;
		    $map[] = $primary;
		    $revmap[$primary] = $reqest;
		}
	    $res->free();
	    
	    $res = QBContent::getBasicInformation($map);
	    while ($erg = $res->fetch())
		{
		    list($title, $pubdate, $alias) = $erg;
		    $infos[$revmap[$alias]] = array(
		        'Title' => $title, 
				'Alias' => $alias,
				'PubDate' => strtotime($pubdate)
			);
		}
		$res->free();
		return $infos;
	}
	
	//content chains
	
	public function chainContentsToClass($class, array $aliases)
	{
	    return QBContent::chainContensToClass(is_object($class) ? get_class($class) : $class, $aliases);
	}
	
	public function getContentsChainedToClass($class)
	{
	    $res = QBContent::getContentsChainedToClass(is_object($class) ? get_class($class) : $class);
	    $guids = array();
	    while($row = $res->fetch())
	    {
	        $guids[$row[0]] = $row[0];
	    }
	    $res->free();
	    return $guids;
	}
	
	public function releaseContentChainsToClass($class, $aliases = null)
	{
	    if(is_array($aliases) || $aliases == null)
	    {
	        QBContent::releaseContensChainedToClass(is_object($class) ? get_class($class) : $class, $aliases);
	        return true;
	    }
	    return false;
	}
}
?>