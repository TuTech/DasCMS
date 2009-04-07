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