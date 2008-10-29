<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.11.2007
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
		$Tags,	
		$Description,//meta description - plain text
		$Size
		;
	/**
	 * @var QSpore
	 */
	protected $invokingQueryObject = null;
		
	protected function initBasicMetaFromDB($alias)
	{
	    list($id, $ttl, $pd, $desc, $tags) = QBContent::getBasicMetaData($alias);
	    $this->Id = $id;
	    $this->Title = $ttl;
	    $this->PubDate = strtotime($pd);
	    $this->_origPubDate = $this->PubDate;
	    $this->Description = $desc;
	    $this->Tags = $tags;
	    $this->Alias = $alias;
	}
	
	protected function initAdditionalMetaFromDB($alias)
	{
	    $this->_loadLazyData = array();
	    list($cb, $cd, $mb, $md, $sz) = QBContent::getAdditionalMetaData($alias);
	    $this->CreatedBy = $cb;
	    $this->CreateDate = strtotime($cd);
	    $this->ModifiedBy = $mb;
	    $this->ModifyDate = strtotime($md);
	    if(empty($this->Size))
	    {
	        $this->Size = $sz;
	    }
	}
	
	protected function saveMetaToDB()
	{
	    QBContent::saveMetaData($this->Id, $this->Title, $this->PubDate, $this->Description, $this->Size);
	}
	
	protected $_loadLazyData = array('CreatedBy', 'CreateDate', 'ModifiedBy', 'ModifyDate', 'Size');
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    throw new Exception('not implemented');
	    //FIXME
	}
		
	public static function Open($alias)
	{
	    try
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
	    catch(Exception $e)
	    {
	        return CError::Open(404);
	    }
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
	public function getTitle()
	{
		return $this->Title;
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
		if($this->Tags == null)
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
		return $this->CreatedBy;
	}
	
		/**
	 * @return string
	 */
	public function getModifiedBy()
	{
		return $this->ModifiedBy;
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
		return $this->CreateDate;
	}
	
	/**
	 * @return int
	 */
	public function getModifyDate()
	{
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