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
abstract class BContent extends BObject implements Interface_Content
{
	protected $_origPubDate;

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
		$MimeType
		;

	/////////
	//Linking
    private $parentView = null;

	public function setParentView(VSpore $pv)
	{
		$this->parentView = $pv;
	}

	/**
	 * @return VSpore
	 */
	public function getParentView()
	{
		return $this->parentView;
	}

	//Linking
	/////////

	////////////
	//Composites

	const COMPOSITE_PREFIX = 'Model_Content_Composite_';

	/**
	 * this must not be used outside of BContent::composites()
	 * @var array
	 */
	protected static $_composites = null;

	/**
	 * compIndex => method
	 * @var array
	 */
	protected $_compositeMethodLookup = null;

	/**
	 * complete list of composites loaded from composites()
	 * compIndex => compName
	 * @var array
	 */
	protected $_compositeLookup = null;

	/**
	 * complete list of composites loaded from composites()
	 * compIndex => compClass
	 * @var array
	 */
	protected $loadedComposites = array();


	/**
	 * overwrite for more composites
	 * compIndex => compName
	 * @return array
	 */
	protected function composites()
	{
		if(self::$_composites == null){
			$classes = Core::getClassesWithInterface('Interface_Composites_AutoAttach');
			self::$_composites = array();
			foreach ($classes as $class){
				if(substr($class, 0, strlen(self::COMPOSITE_PREFIX)) == self::COMPOSITE_PREFIX){
					self::$_composites[] = substr($class, strlen(self::COMPOSITE_PREFIX));
				}
			}
		}
	    return self::$_composites;
	}

	protected function initComposites()
	{
	    if($this->_compositeMethodLookup === null)
	    {
	        //init
	        $contentType = get_class($this);
	        $this->_compositeLookup = array_unique($this->composites());
	        $this->_compositeMethodLookup = array();

	        //walk through attached composites
	        foreach ($this->_compositeLookup as $index => $comp)
	        {
	            //build class and lookup names
	            $class = BContent::COMPOSITE_PREFIX.$comp;
	            if(class_exists($class, true))
	            {
    	            $lookup = $class.'::getCompositeMethods';
    	            //check lookup
    	            if(is_callable($lookup, false, $lookup))
    	            {
    	                //get the implemented composite methods
    	                $methods = call_user_func($lookup, $contentType);
    	                if(is_array($methods))
    	                {
    	                    foreach ($methods as $method)
    	                    {
    	                        //link the methods to the composite
    	                        $this->_compositeMethodLookup[$method] = $index;
    	                    }
    	                }
    	            }
    	        }
	        }
	    }
	}

	public function attachComposite(Interface_Composites_Attachable $composite)
	{
	    $this->initComposites();
	    #echo 'attach->';
	    $class = get_class($composite);
	    $contentType = get_class($this);
	    $lookup = $class.'::getCompositeMethods';
        //check lookup
        if(is_callable($lookup, false, $lookup))
        {
            //get the implemented composite methods
            $methods = call_user_func($lookup, $contentType);
            $compName = substr($class, strlen(BContent::COMPOSITE_PREFIX));
            if(is_array($methods) && !in_array($compName, $this->_compositeLookup))
            {
                $index = count($this->_compositeLookup);
                $this->_compositeLookup[$index] = $compName;
                if($composite->attachedToContent($this))
                {
                    $this->loadedComposites[$index] = $composite;
                    #echo 'ATTACHED';
                    foreach ($methods as $method)
                    {
                        //link the methods to the composite
                        $this->_compositeMethodLookup[$method] = $index;
                    }
                    #print_r($this->_compositeLookup);
                    #print_r($this->_compositeMethodLookup);
                }
            }
        }
	}


	protected function hasMethod($method)
	{
	    $this->initComposites();
	    return method_exists($this, $method) || isset($this->_compositeMethodLookup[$method]);
	}

	public function hasComposite($composite)
	{
	    $this->initComposites();
	    return in_array($composite, $this->_compositeLookup);
	}

	protected function getCompositeForIndex($index)
	{
	    $this->initComposites();
	    if(!isset($this->loadedComposites[$index]))
	    {
	        //check composite
    	    if(!isset($this->_compositeLookup[$index]))
    	    {
    	        throw new XUndefinedIndexException('composite not found');
    	    }

    	    //check composite class
    	    $compositeClass = BContent::COMPOSITE_PREFIX.$this->_compositeLookup[$index];
    	    if(!class_exists($compositeClass, true))
    	    {
    	        throw new XUndefinedException('composite not found');
    	    }

    	    //init composite class
    	    $this->loadedComposites[$index] = new $compositeClass($this);
	    }
	    return $this->loadedComposites[$index];
	}

	public function __call($method, $args)
	{
	    $this->initComposites();
	    if(isset($this->_compositeMethodLookup[$method]))
	    {
	        $comp = $this->getCompositeForIndex($this->_compositeMethodLookup[$method]);
	        return call_user_func_array(array($comp, $method), $args);
	    }
	}

	//Composites
	////////////

	/**
	 * load some data from db
	 * @param string $alias
	 * @return void
	 */
	protected function initBasicMetaFromDB($alias)
	{
		$res = Core::Database()
			->createQueryForClass('BContent')
			->call('basicMeta')
			->withParameters($alias);
		list(
				$this->Id,
				$this->Title,
				$pd,
				$this->Description,
				$this->MimeType,
				$this->Size,
				$this->GUID,
				$this->Alias,
				$this->SubTitle
			) = $res->fetchResult();
		$res->free();

		//parse pubdate
		$this->PubDate = ($pd == '0000-00-00 00:00:00' ? 0 : strtotime($pd));
		$this->_origPubDate = $this->PubDate;

		//load tags
	    $this->Tags = Core::Database()
			->createQueryForClass('BContent')
			->call('tags')
			->withParameters($this->Id)
			->fetchList();
	}

	/**
	 * save meta data to db
	 * @return void
	 */
	protected function saveMetaToDB()
	{
	    QBContent::saveMetaData($this->Id, $this->Title, $this->PubDate, $this->Description, $this->Size, $this->SubTitle);
	}

	protected static function isIndexingAllowed($contentID)
	{
		return !!Core::Database()
			->createQueryForClass('BContent')
			->call('searchable')
			->withParameters($contentID)
			->fetchSingleValue();
	}

	protected static function setIndexingAllowed($contentID, $isAllowed)
	{
		Core::Database()
			->createQueryForClass('BContent')
			->call('setSearchable')
			->withParameters($isAllowed ? 'Y' : 'N', $contentID)
			->execute();
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
		$var = ucfirst($var);
		if($this->hasMethod('get'.$var))
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
		$var = ucfirst($var);
		if($this->hasMethod('set'.$var))
		{
		    $this->__get($var); //trigger autoloads
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
		return $this->hasMethod('get'.ucfirst($var));
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
	 * allowed html: <b><i><u><s><strong><sub><sup><small><br>
	 * @param string $value
	 */
	public function setSubTitle($value)
	{
	    //replace unwanted tags
	    //$value = preg_replace('/<\s*\/?\s*(!?:(b|i|u|s|sub|sup))\s*>/mui', '', $value);
	    $value = strip_tags($value, '<b><i><u><s><strong><sub><sup><small><br>');
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

	//public abstract function Save();

	protected abstract function saveContentData();

	public function Save()
	{
		$e = new EWillSaveContentEvent($this, $this);
		if($e->isCanceled()){
			return;//notifications are up to the canceling object
		}

		$this->saveContentData();

	    $this->setModifiedBy(PAuthentication::getUserID());
		$this->setModifyDate(time());
	    $this->saveMetaToDB();
	    //foreach loaded composite: ->save
	    foreach ($this->loadedComposites as $composite) {
	    	$composite->contentSaves();
	    }
		$e = new EContentChangedEvent($this, $this);
		if($this->_origPubDate != $this->PubDate)
		{
			$e = ($this->getPubDate() == 0)
				? new EContentRevokedEvent($this, $this)
				: new EContentPublishedEvent($this, $this);
		}
	}
}
?>
