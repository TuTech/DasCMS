<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-29
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Navigator
 */
class NTreeNavigationHelper 
{
	const TITLE = 0;
	const ALIAS = 1;
	const IS_PUBLIC = 2;

	public $spore = null;
	public $root = null;
	public $content = null;
	private $currentAlias = null;
	private $_activeNodes = array();
	
	private $_nodeData = array();
	
	/**
	 * Constructor
	 *
	 * @param NTreeNavigationObject $tno
	 * @param Controller_View_Content $spore
	 */
	public function __construct(NTreeNavigationObject $tno, Controller_View_Content $spore)
	{
	    //gather all aliases, 
	    //NTreeNavigationHelper->getContentCMSID() = SAlias::getMatching(id, aliases[])
	    
		$this->spore = $spore;
		$this->root = $tno;
    	$content = $this->spore->getContent();
    	//no content found?
    	if($content == null || !$content instanceof Interface_Content)
    	{
    		$content = $this->spore->getErrorContent();
    	}
    	//no error content defined?
    	if($content == null || !$content instanceof Interface_Content)
    	{
    		$content = new CError(404);
    	}
    	$this->content = $content;
    	$allAliases = $this->root->getAllAliases($this);
    	$this->currentAlias = SAlias::getMatching($this->content->getId(), $allAliases);
    	$this->root->initTree($this);
    	if(count($this->_activeNodes) == 0 && $this->root->hasChildren())
    	{
    	    //activate first element
    	    $this->_activeNodes[] = $this->root->getFirstChild();
    	}
    	$initialNodes = $this->_activeNodes;//_active nodes will be afterwards but just activate initial
    	foreach ($initialNodes as $node) 
    	{
//    		echo 'active node ', $node->getAlias().'<br />';
    		$node->activate();
    	}
    	//all active nodes have reported their presence
    	$cmsids = array();
    	foreach ($this->_activeNodes as $node) 
    	{
    		$cmsids[$node->getAlias()] = '';
    	}
    	$this->_nodeData = Controller_Content::getInstance()->getContentInformationBulk(array_keys($cmsids));
	}
	
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->root);
	}
	
	/**
	 * get cmsid of active content
	 *
	 * @return string
	 */
	public function getContentCMSID()
    {
    	if($this->content == null)
    	{
    		throw new XUndefinedIndexException('not initialized');
    	}
    	return $this->currentAlias;
    }
    
    /**
     * generate link to nav object
     *
     * @param NTreeNavigationObject $tno
     * @return string
     */
    public function linkTo(NTreeNavigationObject $tno)
    {
    	return $this->spore->linkTo($this->getAlias($tno));
    }
    
    /**
     * is element visible/accessable
     *
     * @param NTreeNavigationObject $tno
     * @return boolean
     */
    public function isAccessable(NTreeNavigationObject $tno)
    {
    	$alias = $tno->getAlias();
    	return (
    		//in active nodes array?
    		isset($this->_nodeData[$alias])
    		//public?
    		&& !empty ($this->_nodeData[$alias][self::IS_PUBLIC])
    	);
    }
    
    /**
     * set element active/visible
     *
     * @param NTreeNavigationObject $tno
     */
    public function setActiveNode(NTreeNavigationObject $tno)
    {
    	$this->_activeNodes[] = $tno;
    }
    
    /**
     * is element pointing to the current content
     *
     * @param NTreeNavigationObject $tno
     * @return boolean
     */
    public function isSelectedElement(NTreeNavigationObject $tno)
    {
    	if($this->content == null)
    	{
    		throw new XUndefinedIndexException('not initialized');
    	}
    	return $tno->getAlias() == $this->getContentCMSID();
    }
    
    /**
     * @param NTreeNavigationObject $tno
     * @return string
     */
    public function getTitle(NTreeNavigationObject $tno)
    {
    	return (
    		//in active nodes array?
    		isset($this->_nodeData[$tno->getAlias()])
    		&& isset($this->_nodeData[$tno->getAlias()][self::TITLE])
    	)
    	? $this->_nodeData[$tno->getAlias()][self::TITLE]
    	: '';
    }
     
    /**
     * get currently active alias (prefetched from SAlias)
     *
     * @param NTreeNavigationObject $tno
     * @return string
     */
    public function getAlias(NTreeNavigationObject $tno)
    {
    	return (
    		//in active nodes array?
    		isset($this->_nodeData[$tno->getAlias()])
    		&& isset($this->_nodeData[$tno->getAlias()][self::ALIAS])
    	)
    	? $this->_nodeData[$tno->getAlias()][self::ALIAS]
    	: '';
    }
}
?>