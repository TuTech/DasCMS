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
	    return QBContent::deleteContent($alias);
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
	
	public function setContent($value)
	{
	    if (!$value instanceof WCPersonAttributes) 
	    {
	    	throw new XArgumentException('content must be an instance of WCPersonAttributes');
	    }
	    //get attributes, types, contexts from db
	    //foreach att 
	        //get att from $value
	        //add new contexts from $value to DB
	        //foreach value in $value
	            //add entry to db
	            
	    //rebuild attributes from db
	    $this->Content = $value;
	}
	
	/**
	 * returns WCPersonAttributes
	 * @return array
	 */
	private function buildAttributes()
	{
	    $atts = new WCPersonAttributes();
	    
	    $phoneAtt = new WCPersonAttribute('phone', 'phone', array('arbeit', 'mobil', 'privat', 'fax arbeit', 'fax privat'));
	    $phoneAtt->addEntry(new WCPersonEntry($phoneAtt, 'arbeit', '+49 01054 2540 4521'));
	    $phoneAtt->addEntry(new WCPersonEntry($phoneAtt, 'mobil', '+35424424357'));
	    $atts->addAttribute($phoneAtt);
	    
	    $phoneAtt = new WCPersonAttribute('email', 'email', array('arbeit', 'mobil', 'privat'));
	    $phoneAtt->addEntry(new WCPersonEntry($phoneAtt, 'arbeit', 'em@il.de'));
	    $phoneAtt->addEntry(new WCPersonEntry($phoneAtt, 'mobil', 'mob@em.ail'));
	    $atts->addAttribute($phoneAtt);
	    
	    return $atts;
	}
	
	public function Save()
	{
		$this->saveMetaToDB();
		new EContentChangedEvent($this, $this);
		if($this->_origPubDate != $this->PubDate)
		{
			$e = ($this->__get('PubDate') == 0)
				? new EContentRevokedEvent($this, $this)
				: new EContentPublishedEvent($this, $this);
		}
	}
	
	//Login credentials
	public function hasLogin()
	{
	    //count rel-ing
	    return false;
	}
		
	public function getLoginName()
	{
	    //count rel-ing
	    return $this->Title;
	}
	
	public function createLogin($user, $password)
	{
	    //insert
	}
	
	public function removeLogin()
	{
	    //delete rel-ing
	}
	
	public function changePassword($newPassword)
	{
	    //update
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
}
?>