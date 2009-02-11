<?php
/**
 * @package Bambus
 * @subpackage Navigators
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 29.04.2008
 * @license GNU General Public License 3
 */
class NTreeNavigationHelper 
{
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
	 * @param QSpore $spore
	 */
	public function __construct(NTreeNavigationObject $tno, QSpore $spore)
	{
	    //gather all aliases, 
	    //NTreeNavigationHelper->getContentCMSID() = SAlias::getMatching(alias, aliases[])
	    
		$this->spore = $spore;
		$this->root = $tno;
    	$content = $this->spore->getContent();
    	//no content found?
    	if($content == null || !$content instanceof BContent)
    	{
    		$content = $this->spore->getErrorContent();
    	}
    	//no error content defined?
    	if($content == null || !$content instanceof BContent)
    	{
    		$content = CError::Open(404);
    	}
    	$this->content = $content;
    	$allAliases = $this->root->getAllAliases($this);
    	$this->currentAlias = SAlias::getMatching($this->content->Alias, $allAliases);
    	$this->root->InitTree($this);
    	if(count($this->_activeNodes) == 0 && $this->root->hasChildren())
    	{
    	    //activate first element
    	    $this->_activeNodes[] = $this->root->getFirstChild();
    	}
    	$initialNodes = $this->_activeNodes;//_active nodes will be afterwards but just activate initial
    	foreach ($initialNodes as $node) 
    	{
//    		echo 'active node ', $node->getAlias().'<br />';
    		$node->Activate();
    	}
    	//all active nodes have reported their presence
    	$cmsids = array();
    	foreach ($this->_activeNodes as $node) 
    	{
    		$cmsids[$node->getAlias()] = '';
    	}
    	$this->_nodeData = SContentIndex::getContentInformationBulk(array_keys($cmsids));
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
    	return $this->currentAlias;//$this->content->Alias;
    }
    
    /**
     * generate link to nav object
     *
     * @param NTreeNavigationObject $tno
     * @return string
     */
    public function LinkTo(NTreeNavigationObject $tno)
    {
    	return $this->spore->LinkTo($this->getAlias($tno));
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
    		//pub date ok?
    		&& isset($this->_nodeData[$alias]['PubDate'])
    		&& ($this->_nodeData[$alias]['PubDate']) > 0
    		&& ($this->_nodeData[$alias]['PubDate']) <= time()
    	);
    	//@todo permissions
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
    		&& isset($this->_nodeData[$tno->getAlias()]['Title'])
    	)
    	? $this->_nodeData[$tno->getAlias()]['Title']
    	: '';
    }
     
	/**
     * @param NTreeNavigationObject $tno
     * @return string
     */
    public function getPubDate(NTreeNavigationObject $tno)
    {
    	return (
    		//in active nodes array?
    		isset($this->_nodeData[$tno->getAlias()])
    		&& isset($this->_nodeData[$tno->getAlias()]['PubDate'])
    	)
    	? $this->_nodeData[$tno->getAlias()]['PubDate']
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
    		&& isset($this->_nodeData[$tno->getAlias()]['Alias'])
    	)
    	? $this->_nodeData[$tno->getAlias()]['Alias']
    	: '';
    }
}
?>