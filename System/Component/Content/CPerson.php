<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-09
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CPerson
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId,
        ISearchDirectives,
        Interface_XML_Atom_ProvidesInlineText 
{
    const GUID = 'org.bambuscms.content.cperson';
    const CLASS_NAME = 'CPerson';
    public function getClassGUID()
    {
        return self::GUID;
    }

    /**
	 * @return CPerson
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = QBContent::create(self::CLASS_NAME, $title);
	    $user = new CPerson($alias);
	    new EContentCreatedEvent($user, $user);
	    return $user;
	}
	
	public static function Delete($alias)
	{
	    return parent::Delete($alias);
	}
	
	public static function Exists($alias)
	{
	    return parent::contentExists($alias, self::CLASS_NAME);
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    return parent::getIndex(self::CLASS_NAME, false);
	}
	
	public static function IndexWithCompany()
	{
		try
		{
		    $res = QCPerson::getBasicInformation();
			$index = array();
			while ($arr = $res->fetch())
			{
			    list($title, $pubdate, $alias, $type, $id, $company) = $arr; 
				$index[$alias] = array($title, $pubdate, $type, $id, $company);
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
		
	public static function Open($alias)
	{
	    try
	    {
	        return new CPerson($alias);
	    }
	    catch (XArgumentException $e)
	    {
	        throw new XUndefinedIndexException($alias);
	    }
	}
	
	
	/**
	 * @param string $id
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 * @throws XInvalidDataException
	 */
	public function __construct($alias)
	{
	    if(!self::Exists($alias))
	    {
	        throw new XArgumentException('content not found');
	    }
	    $this->initBasicMetaFromDB($alias);
	}
	
	/**
	 * Icon for this filetype
	 * @return WIcon
	 */
	public static function defaultIcon()
	{
	    return new WIcon(self::CLASS_NAME, 'content', WIcon::LARGE, 'mimetype');
	}
	
	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon()
	{
	    return CPerson::defaultIcon();
	}
	
	/**
	 * @return WCPersonAttributes
	 */
	public function getContent()
	{
	    if($this->Content instanceof WCPersonAttributes)
	    {
	        return $this->Content->asContent();
	    }
	    else
	    {
	        return $this->buildAttributes()->asContent();
	    }
	}
	private function p($str)
	{
	    echo "\n\n<!-- ", $str, ' --> ';
	}
	public function setContent($value)
	{
	    if (!$value instanceof WCPersonAttributes) 
	    {
	    	throw new XArgumentException('content must be an instance of WCPersonAttributes');
	    }
	    //start transaction
        QCPerson::begin();
        //delete all data from this person
        QCPerson::resetPersonData($this->Id);
        $this->p('reset');
        //add missing contexts
        $contexts = array();
        foreach($value->getAttributes() as $attribute)
        {
            $contexts = array_merge($contexts, $attribute->getContexts());
        }
        $contexts = array_unique($contexts);
        $this->p('all ctx: '.implode(', ', $contexts));
        $availContexts = array();
        $newContexts = array();
        $cres = QCPerson::availableContexts($contexts);
        while ($crow = $cres->fetch())
        {
            $availContexts[$crow[0]] = 1; 
        }
        $cres->free();
        foreach ($contexts as $ctx)
        {
            if(!isset($availContexts[$ctx]))
            {
                $newContexts[] = $ctx;
            }
        }
        $this->p('new ctx: '.implode(', ', $newContexts));
        //add new contexts
        $this->p('added ctx: '.QCPerson::addContexts($newContexts));
	            
        //set new data for each valid attribute
	    $res = QCPerson::getAttributesWithType();
	    while ($row = $res->fetch())
	    {
	        list($att, $type) = $row;
	        //attribute in sent data?
	        if($value->hasAttribute($att))
	        {
	            $this->p('att: '.$att);
	            $attribute = $value->getAttribute($att);
	            
	            //save entries to database
	            foreach($attribute->getEntries() as $entry)
	            {
	                echo "\n\n<!--Setting ", $att, '/', $entry->getContext(),': ', $entry->getValue(), '-->'; 
	                QCPerson::assignPersonAttributeContextValue(
	                    $this->Id,
	                    $att,
	                    $entry->getContext(),
	                    $entry->getValue()
	                );
	            }
	        }
	    }
	    $res->free();
	    QCPerson::save();
	    //rebuild attributes from db
	    $this->Content = $this->buildAttributes();
	}
	
	/**
	 * returns WCPersonAttributes
	 * @return array
	 */
	private function buildAttributes()
	{
	    //get attributes
	    $atts = new WCPersonAttributes();
	    $res = QCPerson::getAttributesWithType();
	    while ($row = $res->fetch())
	    {
	        list($att, $type) = $row;
	        //get contexts for attribute
	        $ctxres = QCPerson::getAttributeContexts($att);
	        $contexts = array();
	        while($crow = $ctxres->fetch())
	        {
	            if(!empty($crow[0]))
	            {
	                $contexts[] = $crow[0];
	            }
	        }
	        $ctxres->free();
	        //add attribute to list
    	    $Att = new WCPersonAttribute($att, $type, $contexts);
    	    $atts->addAttribute($Att);
	    }
	    $res->free();
	    
	    //get the data for this person
    	$res = QCPerson::getEntriesForPerson($this->Id);
    	while($row = $res->fetch())
    	{
    	    list($att, $ctx, $val) = $row;
    	    if($atts->hasAttribute($att))
    	    {
    	        //add entry
    	        $catt = $atts->getAttribute($att);
    	        $catt->addEntry(new WCPersonEntry($catt, $ctx, $val));
    	    }
    	}    
    	$res->free();
	    return $atts;
	}
	
	public function Save()
	{
		$this->saveMetaToDB();
		$this->saveXAttr();
		new EContentChangedEvent($this, $this);
		if($this->_origPubDate != $this->PubDate)
		{
			$e = ($this->__get('PubDate') == 0)
				? new EContentRevokedEvent($this, $this)
				: new EContentPublishedEvent($this, $this);
		}
	}
	
	//extended person Attributes
	protected $PersonTitle = '',$FirstName = '',$LastName = '',$Company = '';
	protected $xattr_loaded = false;
	protected $xattr_changed = false;
	
	protected function loadXAttr()
	{
	    if(!$this->xattr_loaded)
	    {
	        $this->xattr_loaded = true;
	        $res = QCPerson::getXAttrs($this->getId());
	        if($res->getRowCount())
	        {
	            list(
	                $this->PersonTitle,
	                $this->FirstName,
	                $this->LastName,
	                $this->Company
	            ) = $res->fetch();
	        }
	        $res->free();
	    }
	}
	
	protected function saveXAttr()
	{
	    if($this->xattr_changed)
	    {
	        QCPerson::setXAttrs(
                $this->getId(), 
                $this->PersonTitle,
                $this->FirstName,
                $this->LastName,
                $this->Company
            );
	        $this->xattr_changed = false;
	    }
	}
	
	protected function setXAttr($name, $value)
	{
	    $this->loadXAttr();
	    $this->{$name} = $value;
	    $ttl =  (empty($this->PersonTitle) ? '' : trim($this->PersonTitle).' ');
	    $ttl .= (empty($this->FirstName) ? '' : trim($this->FirstName).' ');
	    $ttl .= (empty($this->LastName) ? '' : trim($this->LastName));
	    $this->setTitle($ttl);
	    $this->xattr_changed = true;
	}
	
	public function getPersonTitle()
	{
	    $this->loadXAttr();
	    return $this->PersonTitle;
	}
	public function setPersonTitle($value)
	{
	    $this->setXAttr('PersonTitle', $value);
	}
	
	public function getFirstName()
	{
	    $this->loadXAttr();
	    return $this->FirstName;
	}
	public function setFirstName($value)
	{
	    $this->setXAttr('FirstName', $value);
	}
	
	public function getLastName()
	{
	    $this->loadXAttr();
	    return $this->LastName;
	}
	public function setLastName($value)
	{
	    $this->setXAttr('LastName', $value);
	}	
	
	public function getCompany()
	{
	    $this->loadXAttr();
	    return $this->Company;
	}
	public function setCompany($value)
	{
	    $this->setXAttr('Company', $value);
	}
	
	
	//Login credentials
	private $hasLogin = null;
	private $loginName = null;
	private $digestHA1;
	private $digestRealm;
	
	/**
	 * load credentials for this person
	 * @return void
	 */
	private function loadLoginCredentials()
	{
	    if($this->hasLogin === null)
	    {
    	    $res = QCPerson::getCredentials($this->getId());
    	    if($res->getRowCount() > 0)
    	    {
    	        $this->hasLogin = true;
    	        list(
    	            $this->loginName,
    	            $this->digestHA1,
    	            $this->digestRealm
    	        ) = $res->fetch();
    	    }
    	    else
    	    {
    	        $this->hasLogin = false;
    	    }
    	    $res->free();
	    }
	}
	/**
	 * @return boolean
	 */
	public function hasLogin()
	{
	    $this->loadLoginCredentials();
	    return $this->hasLogin;
	}
		
	/**
	 * get login name for this person
	 * @return string|null
	 */
	public function getLoginName()
	{
	    $this->loadLoginCredentials();
	    return $this->loginName;
	}
	
	/**
	 * create login for this person
	 * @param string $user
	 * @param string $password
	 * @return boolean
	 * @throws XPermissionDeniedException
	 * @throws Exception
	 */
	public function createLogin($user, $password)
	{
	    if($this->hasLogin())
	    {
	        throw new XPermissionDeniedException('this user already has login credentials', 1);
	    }
	    if(self::isUser($user))
	    {
	        throw new XPermissionDeniedException('this username is already assigned to another person', 2);
	    }
	    $pwLen = LConfiguration::get('password_min_length');
	    if(!empty($pwLen) && is_numeric($pwLen) && !strlen($password) >= $pwLen)
	    {
	        throw new Exception('password to short', 3);
	    }
	    $succ = true;
	    $this->loginName = $user;
	    $this->digestRealm = md5(rand().time().$this->getGUID());
	    $this->digestHA1 = md5($user.':'.$this->digestRealm.':'.$password);
	    try{
	        QCPerson::createCredentials(
	            $this->getId(),
	            $this->loginName,
	            $this->digestHA1,
	            $this->digestRealm
            );
	    }
        catch (XDatabaseException $ed)
        {
            SNotificationCenter::report('warning','could_not_create_login_for_person');
            $succ = false;
        }
        $this->hasLogin = $succ;
        return $succ;
	}
	
	/**
	 * @return int
	 */
	public function removeLogin()
	{
	    $this->hasLogin = false;
	    //FIXME compare roles: delete if target role <= own role - keeps the admins alive
	    return QCPerson::removeLogin($this->getId());
	}
	
	/**
	 * @param $newPassword
	 * @return int
	 */
	public function changePassword($newPassword)
	{
	    if(!$this->hasLogin())
	    {
	        throw new Exception('user needs a login', 1);
	    }
	    $this->digestHA1 = md5($this->loginName.':'.$this->digestRealm.':'.$newPassword);
	    QCPerson::setNewPassword($this->getId(), $this->digestHA1);
	}
	
	/**
	 * @param $password
	 * @return boolean
	 */
	public function validatePassword($password)
	{
	    if(!$this->hasLogin())
	    {
	        throw new Exception('user needs a login', 1);
	    }
	    return $this->digestHA1 === md5($this->loginName.':'.$this->digestRealm.':'.$password);
	}
	
	public function getRole($newPassword)
	{
	    //update
	}
	
	public function assignRole($newPassword)
	{
	    //update
	}
	
	public static function isUser($useName)
	{
	    //update
	    $res = QCPerson::getUser($useName);
	    $isUser = $res->getRowCount() == 1;
	    $res->free();
	    return $isUser;
	}
	
	/**
	 * @param string $loginName
	 * @return CPerson
	 */
	public static function getPersonForLogin($loginName)
	{
	    $res = QCPerson::getAliasForUser($loginName);
	    if($res->getRowCount() == 0)
	    {
	        throw new XUndefinedIndexException('there is no user with this name');
	    }
        list($alias) = $res->fetch();
        return new CPerson($alias);
	}
	
	//Interface_XML_Atom_ProvidesInlineText
    public function getInlineTextType()
    {
        return 'html';
    }
    public function getInlineText()
    {
        return $this->getContent();
    }

    //ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(
		    strtolower($category), 
		    array('text', 'media', 'settings', 'information', 'search')
	    );
	}
	//ISearchDirectives
	public function allowSearchIndex()
	{
	    return BContent::isIndexingAllowed($this->getId());
	}
	public function excludeAttributesFromSearchIndex()
	{
	    return array();
	}
	public function isSearchIndexingEditable()
    {
        return true;
    }
    public function changeSearchIndexingStatus($allow)
    {
        QBContent::setAllowSearchIndexing($this->getId(), !empty($allow));
    }
}
?>