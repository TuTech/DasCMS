<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class CFeed extends BContent implements ISupportsSidebar 
{
	//@todo add rss2/atom
	//BObject->HeaderDirectives: add feeds[rss2|atom]
	const MANAGER = 'MFeedManager';
	const 	TITLE = 1,
			TITLE_AND_SUMMARY = 2,
			TITLE_AND_CONTENT = 3,
			CONTENT = 4,
			DISABLED = 0;
			
	protected 
		$FilterType=null,
		$Filter = null,
		$ItemsPerPage = 15,
		$AllowMultiplePages = true,
		$OverViewMode = CFeed::TITLE_AND_SUMMARY,
		$DetailViewMode = 2 ,
		$LinkAbsolute = false, //use spore main or sub as request
		$ItemCount,
		$PageCount,
		$PageNr
		;
	private $_subContent = null;
	
//	public function __construct($id = null)
//	{
//		//load content for id
//		$manager = MFeedManager::alloc()->init();
//		if($id == null || !$manager->Exists($id))
//		{
//			if($id == null)
//			{
//				$this->Id = $manager->generateId();
//			}
//			else
//			{
//				$this->Id = $id;
//			}
//			$this->Title = '';
//			//Summary: generated
//			//Text: generated
//			$this->PubDate = 0;
//			$this->CreateDate = time();
//			$this->ModifyDate = time();
//		}
//		else
//		{
//			$this->Id = $id;
//		}
//	}
//	
	/**
	 * @param string $id
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 * @throws XInvalidDataException
	 */
	public function __construct($id = null)
	{
		$manager = MFeedManager::alloc()->init(); 
		$meta = array();
		$defaults = array(
			'CreateDate' => time(),
			'CreatedBy' => BAMBUS_USER,
			'ModifyDate' => time(),
			'ModifiedBy' => BAMBUS_USER,
			'PubDate' => 0,
			'Size' => 0,
			'Title' => 'new CFeed '.date('r'),
		);
		if($id == null || !$manager->Exists($id))
		{
			//create
			if($id == null || strlen($id) != 32)
			{
				//invalied page id - generate new id
				$this->Id = $manager->generateId();
			}
			else
			{
				//use requested id
				$this->Id = $id;
			}
			//save settings
			$this->_created = true;
		}
		else
		{
			//set id
			$this->Id = $id;
			$meta = SContentIndex::alloc()->init()->getMeta($this);
		}			
		foreach ($defaults as $var => $default) 
		{
			$this->initPropertyValues($var, $meta, $default);
		}
		$this->_origPubDate = $this->PubDate;
	}
	
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('settings', 'information', 'search'));
	}
	
	public function _get_Title()
	{
		$child = '';
		if(!empty($this->invokingQueryObject))
		{
			$view = $this->invokingQueryObject->GetParameter('view');
			if(!empty($view))
			{
				$this->_subContent = SAlias::resolve($view);
				if($this->_subContent == null)
				{
					$this->_subContent = MError::alloc()->init()->Open(404);
				}
				$child = ' &gt; '.$this->_subContent->_get_Title();
			}
		}		
		return $this->Title.$child;
	}
	

	//Content
	public function _get_Content()
	{
		//@todo build feed html
		$userSearch = ($this->Filter === null);
		//search: SCI::search
		//tags: SCI::getLatest
		$SCI = SContentIndex::alloc()->init();
		if($this->FilterType == 'tags')
		{
			$latest = $SCI->getLatest(
				$this->_get_ItemsPerPage(), 
				($this->_get_PageNr()-1) * $this->_get_ItemsPerPage(),
				null,
				$this->_get_Filter(),
				true,
				true
			);
		}
		else
		{
			$latest = array();
		}
		$feedClass = get_class($this);
		$view = ($this->invokingQueryObject != null &&
			$this->invokingQueryObject instanceof QSpore 
		) ? $this->invokingQueryObject->GetParameter('view') : null;
		if(!empty($view))
		{
			$html = '<div class="'.$feedClass.' '.$feedClass.'-View">'."\n";
			$this->_subContent = SAlias::resolve($view);
			if($this->_subContent == null)
			{
				$this->_subContent = MError::alloc()->init()->Open(404);
			}
			$html .= $this->_subContent->_get_Content();
		}
		else
		{
			$html = '<div class="'.$feedClass.' '.$feedClass.'-List">'."\n";
			//Title,PubDate,Manager,managerContentID AS ContentID
			foreach ($latest as $item) 
			{
	//			if($this->OverViewMode !== self::TITLE)
	//			{
				//resolve manager
				$manager = BObject::InvokeObjectByDynClass($item['Manager']);
				if($manager == null)
				{
					echo 'no manager<br />';
					continue;
				}
				$content = $manager->Open($item['ContentID']);
				if($content == null)
				{
					echo 'no open 1<br />';
					continue;
				}
				$e = new EContentAccessEvent($this,$content);
				if($e->isCanceled())
				{
					continue;
				}
				 
	//			}
				if($this->OverViewMode === self::TITLE)
				{
					$html .= sprintf(
						"\t<div class=\"%s-List-Item\">\n"
						."\t\t<h3 class=\"%s-List-Item-Title\"><a href=\"%s\">%s</a></h3>\n"
						."\t</div>\n"
						,$feedClass
						,$feedClass
						//@todo make $this->LinkAbsolute configurable
						,($this->LinkAbsolute) 
							? $this->linkWithInvokingQueryObject($content->_get_Alias())
							: $this->linkWithInvokingQueryObject($this->_get_Alias(), array(),array('view' => $content->_get_Alias()))
						
						,$item['Title']
					);
				}
				elseif($this->OverViewMode === self::TITLE_AND_SUMMARY)
				{
					$html .= sprintf(
						"\t<div class=\"%s-List-Item\">\n"
						."\t\t<h3 class=\"%s-List-Item-Title\"><a href=\"%s\">%s</a></h3>\n"
						."\t\t<div class=\"%s-List-Item-Summary\">%s</div>\n"
						."\t</div>\n"
						,$feedClass
						,$feedClass
						//@todo make $this->LinkAbsolute configurable
						,($this->LinkAbsolute) 
							? $this->linkWithInvokingQueryObject($content->_get_Alias())
							: $this->linkWithInvokingQueryObject($this->_get_Alias(), array(),array('view' => $content->_get_Alias()))
						,$content->_get_Title()
						,$feedClass
						,$content->_get_Summary()
					);
				}
				elseif($this->OverViewMode === self::TITLE_AND_CONTENT)
				{
					$html .= sprintf(
						"\t<div class=\"%s-List-Item\">\n"
						."\t\t<h3 class=\"%s-List-Item-Title\"><a href=\"%s\">%s</a></h3>\n"
						."\t\t<div class=\"%s-List-Item-Content\">%s</div>\n"
						."\t</div>\n"
						,$feedClass
						,$feedClass
						//@todo make $this->LinkAbsolute configurable
						,($this->LinkAbsolute) 
							? $this->linkWithInvokingQueryObject($content->_get_Alias())
							: $this->linkWithInvokingQueryObject($this->_get_Alias(), array(),array('view' => $content->_get_Alias()))
						
						,$content->_get_Title()
						,$feedClass
						,$content->_get_Content()
					);
				}
				else
				{
					echo 'tpl mode<br />';
					//template
					//parseobject - all needed is in $content 
				}
			}
		}
		$html .= "</div>\n";
		
		return $html;
	}
	
	public function _get_ItemCount()
	{
		//@todo add fulltext search logic
		$sci = SContentIndex::alloc()->init();
		return $sci->countLatest(null,$this->Filter);
	}
	
	public function _get_PageCount()
	{
		if($this->ItemsPerPage == 0)
		{
			return 1;
		}
		else
		{
			return ceil($this->_get_ItemCount()/$this->ItemsPerPage);
		}
	}
	
	public function _get_PageNr()
	{
		//@todo add multipage logic
		return 1;
	}
	
	//FilterType
	public function _set_FilterType($value)
	{
		$this->FilterType = ($value == 'search') ? 'search' : 'tags';
	}
	
	public function _get_FilterType()
	{
		return $this->FilterType;
	}
	
	//Filter string
	public function _set_Filter($value)
	{
		$this->Filter = $value;
	}
	
	public function _get_Filter()
	{
		return $this->Filter;
	}
	
	//Items per page
	public function _set_ItemsPerPage($value)
	{
		if(is_numeric($value) && $value >= 0)
		{
			$this->ItemsPerPage = intval($value);
		}
	}
	
	public function _get_ItemsPerPage()
	{
		return $this->ItemsPerPage;
	}
	
	//allow multiple pages
	public function _set_AllowMultiplePages($value)
	{
		$this->AllowMultiplePages = !empty($value);
	}
	 
	public function _get_AllowMultiplePages()
	{
		return $this->AllowMultiplePages;
	}
	
	//define overview mode
	public function _set_OverViewMode($value)
	{
		if(!empty($value))
		{
			$this->OverViewMode = $value;
		}
	}
	
	public function _get_OverViewMode()
	{
		return $this->OverViewMode;
	}
	
	//define detailview mode
	public function _set_DetailViewMode($value)
	{
		$this->DetailViewMode = $value;
	}
	
	public function _get_DetailViewMode()
	{
		return $this->DetailViewMode;
	}
	
	/**
	 * Get a list of all possible meta keys
	 *
	 * @return array
	 */
	public static function MetaKeys()
	{
		return array( 
		'Id',
		'Title',
		'Summary',
		'Content',
		'Text',
		'Alias',
		'PreviousAliases',
		'PubDate',
		'CreateDate',
		'ModifyDate',
		'Source',
		'Keywords',
		'Description',
		'FilterType',
		'Filter',
		'ItemsPerPage',
		'AllowMultiplePages',
		'OverViewMode',
		'DetailViewMode',
		'Tags',
		'ItemCount',
		'PageCount',
		'PageNr'
		);
	}
	
	public function __sleep()
	{
		return self::MetaKeys();
	}
	
	public function Save()
	{
		$this->getManager()->saveFeed($this);
	}
	
	/**
	 * initialized MPageManager object
	 *
	 * @return MFeedManager
	 */
	public function getManager()
	{
		return MFeedManager::alloc()->init();
	}	
	public function getManagerName()
	{
		return self::MANAGER;
	}
}
?>