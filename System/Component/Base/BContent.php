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
	
	//Properties - to be handled in __get() & __set()
	protected 
		$Id, 		//class unique id
		$Title, 	//title of object
		$Summary,	//short description in html
		$Content,	//content and content type e.g. html, mp3, gif ...
		$Text, 		//Text representation of the object for search indexers
		$Alias, 	//this will be used in navigations (unique in cms)
		$PubDate,	//timestamp of (scheduled) publication
		$CreateDate,//creation timestamp of object
		$CreatedBy,
		$ModifyDate,//timestamp: last modified
		$ModifiedBy, 
		$Source,	//where does it come from local|url
		$Keywords,	//meta keywords 
		$Tags,	//meta keywords 
		$Description,//meta description - plain text
		$Size
		;
	protected $invokingQueryObject = null;
		
	/**
	 * Forwarder for getter functions
	 *
	 * @param string $var
	 * @return mixed
	 * @throws XUndefinedIndexException
	 */
	public function __get($var)
	{
		if(method_exists($this, '_get_'.$var))
		{
			return $this->{'_get_'.$var}();	
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
		if(method_exists($this, '_set_'.$var))
		{
			$this->ModifiedBy = PAuthentication::getUserID();
			$this->ModifyDate = time();
			
			$this->_data__set[$var] = true;
			return $this->{'_set_'.$var}($value);	
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
		return method_exists($this, '_get_'.$var);
	}
	
	/**
	 * String representation of this object 
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->_get_Content());
	}
	
	/**
	 * @return string
	 */
	public function _get_Id()
	{
		return $this->Id;
	}
	
	/**
	 * @return string
	 */
	public function _get_Title()
	{
		return $this->Title;
	}
	
	/**
	 * @param string $value
	 */
	public function _set_Title($value)
	{
		if(strlen($value) > 0)
		{
			$this->Title = $value;
		}
	}
	
	/**
	 * @return array
	 */
	public function _get_Tags()
	{
		if($this->Tags == null)
		{
			$this->Tags = STag::alloc()->init()->get($this);
		}
		return $this->Tags;
	}
	
	/**
	 * @return string
	 */
	public function _get_Keywords()
	{
		return implode(', ',$this->_get_Tags());
	}
	
	/**
	 * Enter description here...
	 *
	 * @param array|string $value
	 */
	public function _set_Tags($value)
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
	public function _get_Alias()
	{
		return SAlias::alloc()->init()->getCurrent($this);
	}
	
	/**
	 * @return string
	 */
	public function _get_CreatedBy()
	{
		return $this->CreatedBy;
	}
	
		/**
	 * @return string
	 */
	public function _get_ModifiedBy()
	{
		return $this->ModifiedBy;
	}
	
/**
	 * @return int
	 */
	public function _get_PubDate()
	{
		return ($this->PubDate == 0) ? '' : $this->PubDate;
	}
	
	/**
	 * @param int|string $value
	 */
	public function _set_PubDate($value)
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
	public function _get_Source()
	{
		return 'local';
	}
	
	/**
	 * @return int
	 */
	public function _get_CreateDate()
	{
		return $this->CreateDate;
	}
	
	/**
	 * @return int
	 */
	public function _get_ModifyDate()
	{
		return $this->ModifyDate;
	}
	
	/**
	 * @return string
	 */
	public function _get_Content()
	{
		return $this->Content;
	}
	
	/**
	 * @param string $value
	 */
	public function _set_Content($value)
	{
		$this->Content = $value;
	}
	
	/**
	 * @return string
	 */
	public function _get_Description()
	{
		return $this->Description;
	}
	
	/**
	 * @param string $value
	 */
	public function _set_Description($value)
	{
		$this->Description = $value;
	}
	
	/**
	 * @return string
	 */
	public function _get_Summary()
	{
		return $this->_get_Description();
	}
	
	/**
	 * @param string $value
	 */
	public function _set_Summary($value)
	{
		$this->_set_Description($value);
	}
	/**
	 * @return string
	 */
	public function _get_Text()
	{
		return strip_tags($this->_get_Content());
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
	
	/**
	 * responsible (initialized) manager object
	 * @return BContentManager
	 */
	public abstract function getManager();
}
?>