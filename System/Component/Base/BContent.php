<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-19
 * @license GNU General Public License 3
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
		$MimeType
		;
	/**
	 * @var QSpore
	 */
	protected $invokingQueryObject = null;
		
	protected function initBasicMetaFromDB($alias)
	{
	    list($id, $ttl, $pd, $desc, $tags, $mt, $sz, $guid) = QBContent::getBasicMetaData($alias);
	    $this->Id = $id;
	    $this->Title = $ttl;
	    $this->PubDate = ($pd == '0000-00-00 00:00:00' ? 0 : strtotime($pd));
	    $this->_origPubDate = $this->PubDate;
	    $this->Description = $desc;
	    $this->Tags = $tags;
	    $this->Alias = $alias;
	    $this->MimeType = $mt;
	    $this->Size = $sz;
	    $this->GUID = $guid;
	}
	
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
	
	protected static function setMimeType($alias, $mime)
	{
	    QBContent::setMimeType($alias, $mime);
	}
	
	protected function saveMetaToDB()
	{
	    QBContent::saveMetaData($this->Id, $this->Title, $this->PubDate, $this->Description, $this->Size);
	}
	
	protected $_loadLazyData = array('CreatedBy', 'CreateDate', 'ModifiedBy', 'ModifyDate');
	
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
	 * @return BContent
	 */
	public static function OpenIfPossible($alias)
	{
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
	
	public static function Access($alias, BObject $from)
	{
	    $o = self::Open($alias);
	    $e = new EWillAccessContentEvent($from, $o);
	    if($e->hasContentBeenSubstituted())
	    {
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
	    if(in_array($var, $this->_loadLazyData))
	    {
	        $this->initAdditionalMetaFromDB($this->Alias);
	    }
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
	 * @return string
	 */
	public function getTitle()
	{
		return $this->Title;
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
	 * @return array
	 */
	public function getTags()
	{
		if($this->Tags === null)
		{
			$this->Tags = STag::alloc()->init()->get($this);
		}
		return $this->Tags;
	}
	
	/**
	 * Enter description here...
	 *
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
	
	public function InvokedByQueryObject(QSpore $qo)
	{
		$this->invokingQueryObject = $qo;
	}
	
	protected function linkWithInvokingQueryObject($to, array $opts = array(), array $tempopts = array())
	{
		if($this->invokingQueryObject != null && $this->invokingQueryObject instanceof QSpore)
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
