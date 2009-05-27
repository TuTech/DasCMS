<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
 */
abstract class BContent extends BObject
{
	protected 
		$_data__set = array(),
		$_origPubDate;
	protected $_modified = false;
		
	//Properties - to be handled in __get() & __set()
	protected 
		$Id, 		//class unique id
		$GUID,      //Global Unique ID
		$Title, 	//title of object
		$SubTitle,
		$Content,	//content and content type e.g. html, mp3, gif ...
		$Text, 		//Text representation of the object for search indexers
		$Alias, 	//this will be used in navigations (unique in cms)
		$PubDate,	//timestamp of (scheduled) publication
		$CreateDate,//creation timestamp of object
		$CreatedBy,
		$ModifyDate,//timestamp: last modified
		$ModifiedBy, 
		$Source,	//where does it come from local|url
		$Tags = null,	
		$Description,//meta description - plain text
		$Size,
		$MimeType,
		$Location,
		$LastAccess = null,
//		$AccessCountDay,
//		$AccessCountWeek,
//		$AccessCountMonth,
//		$AccessCountYear,
		$AccessCount = null,
		$AccessIntervalAverage = null
		;
	/**
	 * @var VSpore
	 */
	protected $invokingQueryObject = null;
		
	protected $_loadLazyData = array('CreatedBy', 'CreateDate', 'ModifiedBy', 'ModifyDate');
	
	/**
	 * load some data from db
	 * @param string $alias
	 * @return void
	 */
	protected function initBasicMetaFromDB($alias)
	{
	    list($id, $ttl, $pd, $desc, $tags, $mt, $sz, $guid, $sttl) = QBContent::getBasicMetaData($alias);
	    $this->Id = $id;
	    $this->Title = $ttl;
	    $this->SubTitle = $sttl;
	    $this->PubDate = ($pd == '0000-00-00 00:00:00' ? 0 : strtotime($pd));
	    $this->_origPubDate = $this->PubDate;
	    $this->Description = $desc;
	    $this->Tags = $tags;
	    $this->Alias = $alias;
	    $this->MimeType = $mt;
	    $this->Size = $sz;
	    $this->GUID = $guid;
	}
	
	/**
	 * load more metadata from db
	 * @param string $alias
	 * @return void
	 */
	protected function initAdditionalMetaFromDB($alias)
	{
	    if(count($this->_loadLazyData))
	    {
    	    $this->_loadLazyData = array();
    	    list($cb, $cd, $mb, $md, $sz) = QBContent::getAdditionalMetaData($alias);
    	    $this->CreatedBy = $cb;
    	    $this->CreateDate = strtotime($cd);
    	    $this->ModifiedBy = $mb;
    	    $this->ModifyDate = strtotime($md);
	    }
	}
	
	/**
	 * @param string $alias
	 * @param string $mime
	 * @return void
	 */
	protected static function setMimeType($alias, $mime)
	{
	    QBContent::setMimeType($alias, $mime);
	}
	
	protected function bindSelfToView($viewname)
	{
	    //if name == '' -> delete
	    //else insert/update view
	    if(empty($viewname))
	    {
	        QBContent::removeViewBinding($this->getId());
	    }
	    else
	    {
	        QBContent::setViewBinding($this->getId(), $viewname);
	    }
	} 
	
	/**
	 * @return string|null
	 */
	protected function getBoundView()
	{
	    $res = QBContent::getViewBinding($this->getId());
	    $view = null;
	    if($res->getRowCount() == 1)
	    {
	        list($view) = $res->fetch(); 
	    }
	    $res->free();
	    return $view;
	}
	///////////
	//chanining
	
	//content chained to a class
	public static function chainContentsToClass($class, array $aliases)
	{
	    QBContent::chainContensToClass(is_object($class) ? get_class($class) : $class, $aliases);
	}
	
	public static function getContentsChainedToClass($class)
	{
	    $res = QBContent::getContentsChainedToClass(is_object($class) ? get_class($class) : $class);
	    $guids = array();
	    while($row = $res->fetch())
	    {
	        $guids[$row[0]] = $row[0];
	    }
	    return $guids;
	}
	
	public static function releaseContentChainsToClass($class, $aliases = null)
	{
	    if(is_array($aliases) || $aliases == null)
	    {
	        QBContent::releaseContensChainedToClass(is_object($class) ? get_class($class) : $class, $aliases);
	    }
	}
	
	//content to other content by a class
	//...
	
	//end chaining
	//////////////
	
	/**
	 * save meta data to db
	 * @return void
	 */
	protected function saveMetaToDB()
	{
	    QBContent::saveMetaData($this->Id, $this->Title, $this->PubDate, $this->Description, $this->Size, $this->SubTitle);
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    throw new Exception('not implemented');
	    //FIXME BContent::Index() not implemented
	}
		
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function GUIDIndex($ofClass)
	{
	    $index = array();
	    $res = QBContent::getGUIDIndexForClass($ofClass);
	    while ($row = $res->fetch())
	    {
	        $index[$row[0]] = $row[1];
	    }
	    return $index;
	}
	
	protected static function Delete($alias)
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
	
	protected static function isIndexingAllowed($contentID)
	{
	    $res = QBContent::getAllowSearchIndexing($contentID);
	    $allowed = $res->getRowCount() == 1;
	    $res->free();
	    return $allowed;
	}
	
	public static function contentExists($alias, $asType = null)
	{
	    $res = QBContent::exists($alias, $asType);
	    $c = $res->getRowCount();
	    $res->free();
	    return $c == 1;
	}
	
	public static function getContentInformationBulk(array $aliases)
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
	
	/**
	 * @return array (alias => Title)
	 */
	public static function getIndex($class, $simple = true)
	{
		try
		{
		    $res = QBContent::getBasicInformationForClass($class);
			$index = array();
			while ($arr = $res->fetch())
			{
			    list($title, $pubdate, $alias, $type, $id) = $arr; 
				$index[$alias] = $simple ? $title : array($title, $pubdate, $type, $id);
			}
			$res->free();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$index = array();
		}
		return $index;
	}
	
	/**
	 * open content for alias or error 404 if permission denied
	 * @param string $alias
	 * @return BContent
	 */
	public static function Open($alias)
	{
	    try
	    {
	        return self::OpenIfPossible($alias);
	    }
	    catch(Exception $e)
	    {
            return CError::Open(404);
	    }
	}
	/**
	 * open content or throw exception if permission deied
	 * @param string $alias
	 * @throws XInvalidDataException
	 * @return BContent
	 */
	public static function OpenIfPossible($alias)
	{
	    if(empty($alias))
	    {
	        throw new XUndefinedException('no alias');
	    }
        $class = QBContent::getClass($alias);
        if(class_exists($class, true))
        {
            return call_user_func_array($class.'::Open', array($alias));
        }
        else
        {
            throw new XInvalidDataException($class.' not found');
        }
	}
	
	/**
	 * opens content and sends access events 
	 * @param string $alias
	 * @param BObject $from
	 * @param boolean $exact 
	 * @return BContent
	 */
	public static function Access($alias, BObject $from, $exact = false)
	{
	    $o = self::Open($alias);
	    $e = new EWillAccessContentEvent($from, $o);
	    if($e->hasContentBeenSubstituted())
	    {
	        if($exact)
	        {
	            throw new XPermissionDeniedException('content substituted');
	        }
	        $o = $e->Content;
	    }
	    $e = new EContentAccessEvent($from, $o);
	    return $o;
	}
	
	/**
	 * Forwarder for getter functions
	 *
	 * @param string $var
	 * @return mixed
	 * @throws XUndefinedIndexException
	 */
	public function __get($var)
	{
		if(method_exists($this, 'get'.$var))
		{
			return $this->{'get'.$var}();	
		}
		else
		{
			throw new XUndefinedIndexException($var.' not in object');
		}
	}
	
	/**
	 * Forwarder for setter functions
	 *
	 * @param string $var
	 * @param mixed $value
	 * @return void
	 * @throws XPermissionDeniedException
	 */
	public function __set($var, $value)
	{
		if(method_exists($this, 'set'.$var))
		{
		    $this->__get($var); //trigger autoloads
			$this->ModifiedBy = PAuthentication::getUserID();
			$this->ModifyDate = time();
			$this->_modified = true;
			$this->_data__set[$var] = true;
			return $this->{'set'.$var}($value);	
		}
		else
		{
			throw new XPermissionDeniedException($var.' is read only');
		}
	}
	
	/**
	 * Chech existance of getter function for $var
	 *
	 * @param string $var
	 * @return boolean
	 */
	public function __isset($var)
	{
		return method_exists($this, 'get'.$var);
	}
	
	/**
	 * String representation of this object 
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->getContent());
	}
	
	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->Id;
	}
	
	/**
	 * @return string
	 */
	public function getGUID()
	{
		return $this->GUID;
	}
	
	/**
	 * Icon for this filetype
	 * @return WIcon
	 */
	public static function defaultIcon()
	{
	    return new WIcon('BContent', 'content', WIcon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon()
	{
	    return BContent::defaultIcon();
	}
	
	protected function loadBContentAccessStats()
	{
	    if($this->LastAccess === null)
	    {
	        $res = QBContent::getAccessStats($this->getId());
	        if($res->getRowCount() > 0)
	        {
	            list(
	                $firstAccess,//ignore this
	                $this->LastAccess,
	                $this->AccessCount,
	                $this->AccessIntervalAverage
	            ) = $res->fetch();
	        }
	        else
	        {
	            $this->LastAccess = 0;
	            $this->AccessCount = 0;
	            $this->AccessIntervalAverage = 0;
	        }
	        $res->free();
	    }
	}
	
	/**
	 * return last access time
	 * @return int
	 */
	public function getLastAccess()
	{
	    $this->loadBContentAccessStats();
	    return $this->LastAccess;
	}
	
	/**
	 * @return int
	 */
	public function getAccessCount($since = null)
	{
	    $this->loadBContentAccessStats();
	    return $this->AccessCount;
	}
	
	/**
	 * @return int
	 */
	public function getAccessIntervalAverage()
	{
	    $this->loadBContentAccessStats();
	    return $this->AccessIntervalAverage;
	}
	
/**
	 * Icon for this object
	 * @return WImage
	 */
	public function getPreviewImage()
	{
	    return WImage::forContent($this);
	}
	
	public function setPreviewImage($previewAlias)
	{
	    WImage::setPreview($this->getAlias(), $previewAlias);
	}
	
	/**
	 * Icon for this object
	 * @return WContentGeoAttribute
	 */
	public function getLocation()
	{
	    if($this->Location == null)
	    {
	        $this->Location = WContentGeoAttribute::forContent($this);
	    }
	    return $this->Location;
	}
	
	public function setLocation($locationName)
	{
	    $new = WContentGeoAttribute::assignContentLocation($this, $locationName);
	    if($new != null)
	    {
	        $this->Location = $new;
	    }
	}
	
	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->Title;
	}	
	
	/**
	 * @return string
	 */
	public function getSubTitle()
	{
		return $this->SubTitle;
	}
	
	/**
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->MimeType;
	}
	
	/**
	 * @param string $value
	 */
	public function setTitle($value)
	{
		if(strlen($value) > 0)
		{
			$this->Title = $value;
		}
	}
	
	/**
	 * allowed html: <b><i><u><s><sub><sup><small>
	 * @param string $value
	 */
	public function setSubTitle($value)
	{
	    //replace unwanted tags 
	    //$value = preg_replace('/<\s*\/?\s*(!?:(b|i|u|s|sub|sup))\s*>/mui', '', $value);
	    $value = strip_tags($value, '<b><i><u><s><sub><sup><small>');
		$this->SubTitle = $value;
	}
	
	/**
	 * @return array
	 */
	public function getTags()
	{
		if($this->Tags === null)
		{
			$this->Tags = STag::getSharedInstance()->get($this);
		}
		return $this->Tags;
	}
	
	/**
	 * @param array|string $value
	 */
	public function setTags($value)
	{
		if(is_array($value))
		{
			$this->Tags = $value;
		}
		else
		{
			$this->Tags = STag::parseTagStr($value);
		}
	}
	
	/**
	 * @return string
	 */
	public function getAlias()
	{
		return $this->Alias;
	}
	
	/**
	 * @return string
	 */
	public function getCreatedBy()
	{
	    $this->initAdditionalMetaFromDB($this->Alias);
		return $this->CreatedBy;
	}
	
	/**
	 * @return string
	 */
	public function getModifiedBy()
	{
	    $this->initAdditionalMetaFromDB($this->Alias);
		return $this->ModifiedBy;
	}
	/**
	 * @return int
	 */
	public function getSize()
	{
		return $this->Size;
	}
	
	/**
	 * @return int
	 */
	public function getPubDate()
	{
		return ($this->PubDate == 0) ? '' : $this->PubDate;
	}
	
	/**
	 * @param int|string $value
	 */
	public function setPubDate($value)
	{
		if(is_numeric($value) && intval($value) > 0)//timestamp
		{
			$this->PubDate = $value;
		}
		elseif(($dat = @strtotime($value)) !== -1)//time or date string
		{
			$this->PubDate = $dat;
		}
		else
		{
			$this->PubDate = 0;
		}
	}

	/**
	 * @return string
	 */
	public function getSource()
	{
		return 'local';
	}
	
	/**
	 * @return int
	 */
	public function getCreateDate()
	{
	    $this->initAdditionalMetaFromDB($this->Alias);
		return $this->CreateDate;
	}
	
	/**
	 * @return int
	 */
	public function getModifyDate()
	{
	    $this->initAdditionalMetaFromDB($this->Alias);
		return $this->ModifyDate;
	}
	
	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->Content;
	}
	
	/**
	 * @param string $value
	 */
	public function setContent($value)
	{
		$this->Content = $value;
	}
	
	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->Description;
	}
	
	/**
	 * @param string $value
	 */
	public function setDescription($value)
	{
		$this->Description = $value;
	}
	
	/**
	 * @return string
	 */
	public function getText()
	{
		return strip_tags($this->getContent());
	}
	
	public function InvokedByQueryObject(VSpore $qo)
	{
		$this->invokingQueryObject = $qo;
	}
	
	protected function linkWithInvokingQueryObject($to, array $opts = array(), array $tempopts = array())
	{
		if($this->invokingQueryObject != null && $this->invokingQueryObject instanceof VSpore)
		{
			foreach ($opts as $key => $value) 
			{	
				$this->invokingQueryObject->SetLinkParameter($key, $value, false);
			}
			foreach ($tempopts as $key => $value) 
			{	
				$this->invokingQueryObject->SetLinkParameter($key, $value, true);
			}
			return $this->invokingQueryObject->LinkTo($to);
		}
		return '#';
	}
	
	//functions to overwrite
	public abstract function __construct($Id);	//object should load its data here
												//$id is class internal id or cms wide id-path
	public abstract function Save();
	
	public function isModified()
	{
	    return $this->_modified;
	}
}
?>
