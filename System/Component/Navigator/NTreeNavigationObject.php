<?php
/**
 * @package Bambus
 * @subpackage Navigators
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 05.05.2008
 * @license GNU General Public License 3
 */
class NTreeNavigationObject
{
	//tree struct
	public $parent = null;
	public $next = null;
	public $firstChild = null;
	
	public $Navigation = null;
	
	private $spore = null;
	//tree building and linking
	private $alias = '';
	private $accessable = false;
	//in visible part of tree
	private $visible = false;
	//are we current object?
	private $active = false;
	
	public function __sleep()
	{
		if(empty($this->alias) && !$this->isRoot())
		{
			//no content? -> dont save
			return array();
		}
		return array(
			'parent',
			'next',
			'firstChild',
			'spore',
			'alias'
		);
	}
	/**
	 * @return NTreeNavigationObject
	 * @throws XUndefinedIndexException
	 */
	public function getFirstChild()
	{
		if($this->firstChild == null || !$this->firstChild instanceof NTreeNavigationObject)
		{
			throw new XUndefinedIndexException('no first child');
		}
		return $this->firstChild;
	}

	/**
	 * @return boolean
	 */
	public function hasChildren()
	{
		return $this->firstChild !== null;
	}
	
	/**
	 * @return NTreeNavigationObject
	 * @throws XUndefinedIndexException
	 */
	public function getNext()
	{
		if($this->next == null || !$this->next instanceof NTreeNavigationObject)
		{
			throw new XUndefinedIndexException('no sibling');
		}
		return $this->next;
	}

	/**
	 * @return boolean
	 */
	public function hasNext()
	{
		return $this->next !== null;
	}
	
	/**
	 * @return NTreeNavigationObject
	 * @throws XUndefinedIndexException
	 */
	public function getParent()
	{
		if($this->parent == null || !$this->parent instanceof NTreeNavigationObject)
		{
			throw new XUndefinedIndexException('no parent');
		}
		return $this->parent;
	}

	/**
	 * @return boolean
	 */
	public function isRoot()
	{
		return $this->parent === null;
	}
	
	/**
	 * Get CMSID (can, but should not be used as alias)
	 *
	 * @return unknown
	 */
	public function getAlias()
	{
		return $this->alias;
	}
	
	public function setParent($tno)
	{
		$this->parent = ($tno != null && $tno instanceof NTreeNavigationObject)
			? $tno
			: null;
	}
	public function setNext($tno)
	{
		$this->next = ($tno != null && $tno instanceof NTreeNavigationObject)
			? $tno
			: null;
	}
	public function setFirstChild($tno)
	{
		$this->firstChild = ($tno != null && $tno instanceof NTreeNavigationObject)
			? $tno
			: null;
	}
	
	public function __construct($ContentID, $parent, $next, $firstChild)
	{
		$this->alias = $ContentID;
		$this->setParent($parent);
		$this->setNext($next);
		$this->setFirstChild($firstChild);
	}
	
	/**
	 * Build string for nav root mode
	 *
	 * @return string
	 */
	private function rootString()
	{
		return sprintf(
			"<div class=\"NavigationRoot\">\n%s</div>\n"
			,($this->hasChildren()) ? strval($this->firstChild) : ''
		);
	}
	
	/**
	 * Build string for node mode
	 *
	 * @return string
	 */
	private function nodeString()
	{
		if($this->Navigation == null || !$this->Navigation instanceof NTreeNavigationHelper )
		{
			throw new XInvalidDataException('no NTreeNavigation assigned');
		}
		$html = '';
		if($this->Navigation->isAccessable($this))//$this->accessable)
		{
			$html = sprintf(
				"<div class=\"NavigationObject%s%s%s\">\n\t<a href=\"%s\">%s</a>\t"
				,($this->hasChildren())
					? ($this->Navigation->isAccessable($this->getFirstChild()) 
						? ' ExpandedNavigationObject' 
						: ' ExpandableNavigationObject')
					:''
				,($this->Navigation->isSelectedElement($this)) 
					? ' SelectedNavigationObject' 
					: ''
				,' NavigationAlias-'.preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->Navigation->getAlias($this))
				,$this->Navigation->LinkTo($this)
				,htmlentities($this->Navigation->getTitle($this), ENT_QUOTES, 'utf-8')
			);		
			$html .= ($this->hasChildren())
				? sprintf("<div class=\"Children\">%s</div>\n\t",strval($this->firstChild))
				: '';
			$html .= "</div>\n";
		}
		$html .= ($this->hasNext())
			? strval($this->next)
			: '';
		return $html;
	}
	
	public function __toString()
	{
		return ($this->isRoot())
			? $this->rootString()
			: $this->nodeString();
	}
	
	/**
	 * tell all elements in the tree about the NTreeNavigation parenting them
	 * and tell the tree navigation about active nodes
	 *
	 * @param NTreeNavigationHelper $nav
	 */
	public function InitTree(NTreeNavigationHelper $nav)
	{
		$this->Navigation = $nav;
		if($this->alias == $nav->getContentCMSID())
		{
			//report all directly accessed nodes
			$this->reportVisibility();
		}
		if($this->hasChildren())
		{
			$this->firstChild->InitTree($nav);
		}
		if($this->hasNext())
		{
			$this->next->InitTree($nav);
		}
	}
	/**
	 * tell the tree navigation that this is an active node
	 */
	private function reportVisibility()
	{
		if($this->Navigation == null || !$this->Navigation instanceof NTreeNavigationHelper)
		{
			throw new XInvalidDataException('no NTreeNavigation assigned');
		}
		//report all indirectly accessed nodes
		$this->Navigation->setActiveNode($this);
	}
	
	/**
	 * let the children report their visibility
	 */
	private function showChildren()
	{
		try{
			$child = $this->getFirstChild();
			while ($child instanceof NTreeNavigationObject)
			{
				$child->reportVisibility();
				$child = $child->getNext();
			}
		}
		catch (XUndefinedIndexException $e)
		{}
	}
	
	//@todo remove debugging function
	private function debug($cid)
	{
		$dat = explode(':', $cid);
		if(count($dat) == 2)
		{
			list($m, $c) = $dat;
			$r = SContentIndex::getTitleAndAlias($m.':'.$c);
			return $r['Title'];
		}
		return ' <i>ROOT</i> ';
	}
	
	/**
	 * let the parents and their children report their visibility
	 */
	private function showParents()
	{
		
		if(!$this->isRoot())
		{
			$this->getParent()->showChildren();//siblings
			$this->getParent()->showParents();//all the tree up
		}
	}
	
	/**
	 * for the TreeNavigation to tell us we are an accessed element
	 */
	public function Activate()
	{
		$this->showChildren();
		$this->showParents();
	}
}
?>