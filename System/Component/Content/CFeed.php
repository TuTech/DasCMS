<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 17.10.2008
 * @license GNU General Public License 3
 */
class CFeed extends BContent implements ISupportsSidebar, IGlobalUniqueId 
{
    const GUID = 'org.bambuscms.content.cfeed';
    
    public function getGUID()
    {
        return self::GUID;
    }
    
    const HEADER = 0;
    const ITEM = 1;
    const FOOTER = 2;
    const SETTINGS = 3;
    const HEADER_AND_FOOTER = 0;
    private $_contentLoaded = false;
    
    private $_data = array(
        'captions' => array(),
        'attributes' => array(),
        'settings' => array()
    );
    private static $_setable_data = array(
        'captions' => array(
            self::HEADER_AND_FOOTER => array(
                'NumberOfEndSuffix',
                'NumberOfEndPrefix',
                'NumberOfStartSuffix',
                'NumberOfStartPrefix',
                'FountItemsSuffix',
                'FoundItemsPrefix',
                'NextLink',
                'PrevLink',
                'PaginaPrefix',
                'PaginaSuffix'
            ),
            self::ITEM => array(
                'Link'
            )
        ),
        'attributes' => array(
            self::HEADER_AND_FOOTER => array(
                'PrevLink',
                'NextLink',
                'Pagina',
                'NumberOfStart',
                'NumberOfEnd',
                'FoundItems'
            ),
            self::ITEM => array(
                'Desciption',
                'Content',
                'Link',
                'Author',
                'Tags',
                'PubDate',
                'ModDate',
                'Title'
            )
        ),
        'settings' => array(
            self::HEADER_AND_FOOTER => array(
                'PaginaType' => 'b'
			),
            self::ITEM => array(
                'ModDateFormat' => 's',
                'PubDateFormat' => 's',
                'LinkTitle' => 'b',
                'LinkTags' => 'b'
            ),
            self::SETTINGS => array(
                'ElementsPerPage' => 'i',
                'Filter' => 'a',
                'FilterMethod' => 's',
                'TargetView' => 's',
                'SortOrder' => 'b',
                'SortBy' => 's'
                
            )
        )
    );
    
    
    
	/**
	 * @return CFeed
	 */
	public static function Create($title)
	{
	    $SCI = SContentIndex::alloc()->init();
	    list($dbid, $alias) = $SCI->createContent('CFeed', $title);
	    DFileSystem::Save(SPath::TEMPLATES.$dbid.'.php', ' ');
	    //FIXME compile new tpl
	    $tpl = new CFeed($alias);
	    new EContentCreatedEvent($tpl, $tpl);
	    return $tpl;
	}
	
	public static function Delete($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->deleteContent($alias, 'CFeed');
	}
	
	public static function Exists($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->exists($alias, 'CFeed');
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->getIndex('CFeed', false);;
	}
		
	public static function Open($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    if($SCI->exists($alias, 'CFeed'))
	    {
	        return new CFeed($alias);
	    }
	    else
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
	///////////////
	
	private function validateTarget($target)
	{
	    if(!($target == self::HEADER || $target == self::ITEM || $target == self::FOOTER))
	    {
	        throw new XArgumentException('not a valid target');
	    }
	}
	
	public function _setCaptions($target, $name, $caption)
	{
	    $this->validateTarget($target);
	    //FIXME
	}
	
	public function _getCaptions($target, $name)
	{
	    $this->validateTarget($target);
	    //FIXME
	}
	
	public function _setAttributeOrder($target, array $atts)
	{
	    
	}
	
	public function _getAttributeOrder($target, array $atts)
	{
	    
	}
	
	public function _getAvailableAttributes($target)
	{
	    
	}
	
	public function isAttributeActive($target, $att)
	{
	    
	}
	
	public function alterSettings(array $settings)
	{
	    
	}
	
	public function _getSettings()
	{
	    
	}
	
	public function _getSetting($setting)
	{
	    
	}
	
	
	
	
	
	
	
	
	
	
	//////////////
	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	public function getContent()
	{
	    //FIXME invoking query object pagina
	    //FIXME header
	    //FIXME implement fetching
        $res = QCFeed::getItemsForPage();
        //FIXME footer  
	}
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('feeds are generated');
	}
	
	
	public function Save()
	{
		//save content
		if($this->_contentLoaded)
		{
			//FIXME DFileSystem::Save(SPath::TEMPLATES.$this->Id.'.php',$this->RAWContent);
		}
		$this->saveMetaToDB();
		new EContentChangedEvent($this, $this);
		if($this->_origPubDate != $this->PubDate)
		{
			$e = ($this->__get('PubDate') == 0)
				? new EContentRevokedEvent($this, $this)
				: new EContentPublishedEvent($this, $this);
		}
	}
	
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('text', 'media', 'settings', 'information', 'search'));
	}
}
?>