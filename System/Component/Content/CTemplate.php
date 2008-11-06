<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 17.10.2008
 * @license GNU General Public License 3
 */
class CTemplate 
    extends BContent 
    implements 
        ISupportsSidebar, 
        IGlobalUniqueId,
        IPageGenerator  
{
    const GUID = 'org.bambuscms.content.ctemplate';
    public function getGUID()
    {
        return self::GUID;
    }
    protected $RAWContent;
    private $_contentLoaded = false;
    
	/**
	 * @return CTemplate
	 */
	public static function Create($title)
	{
	    $SCI = SContentIndex::alloc()->init();
	    list($dbid, $alias) = $SCI->createContent('CTemplate', $title);
	    DFileSystem::Save(SPath::TEMPLATES.$dbid.'.php', ' ');
	    $tpl = new CTemplate($alias);
	    new EContentCreatedEvent($tpl, $tpl);
	    return $tpl;
	}
	
	public static function Delete($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->deleteContent($alias, 'CTemplate');
	}
	
	public static function Exists($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->exists($alias, 'CTemplate');
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->getIndex('CTemplate', false);;
	}
		
	public static function Open($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    if($SCI->exists($alias, 'CTemplate'))
	    {
	        return new CTemplate($alias);
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
	
	/**
	 * Enter description here...
	 *
	 * @return string
	 */
	public function getContent()
	{
	    //run template
	    try {
            $tpl = new TEngine($this->Id.'.php', BTemplate::CONTENT, array());
    		return $tpl->execute(array());
	    }
	    catch (Exception $e)
	    {
	        return '';
	    }
	}
	
	public function generatePage(array $environment)
	{
	    try {
            $tpl = new TEngine($this->Id.'.php', BTemplate::CONTENT, LConfiguration::as_array());
    		return $tpl->execute($environment);
	    }
	    catch (Exception $e)
	    {
	        return '';
	    }
	}
	
	public function setContent($value)
	{
	    throw new XPermissionDeniedException('compiled templates are read only');
	}
	
	public function setRAWContent($value)
	{
	    //save and compile
		$this->Size = strlen($value);
		$this->_contentLoaded = true;
		$this->RAWContent = $value;
	}
	
	public function getRAWContent()
	{
	    //load
	    if($this->RAWContent == null)
	    {
	        $this->RAWContent = DFileSystem::Load(SPath::TEMPLATES.$this->Id.'.php');
	    }
	    return $this->RAWContent;
	}
	
	public function Save()
	{
		//save content
		if($this->_contentLoaded)
		{
			DFileSystem::Save(SPath::TEMPLATES.$this->Id.'.php',$this->RAWContent);
			if(!empty($this->RAWContent))
			{
			    $tc = new TCompiler($this->Id.'.php', BTemplate::CONTENT);
			    $tc->save();
			}
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