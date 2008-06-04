<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 17.09.2007
 * @license GNU General Public License 3
 */
class News_Channel
{
	//stepping: kind & interval
	private $_steppingMethods = array('all', 'item_count', 'day', 'week', 'month', 'year' );
	
	//to serialize
	private $ID = 0;
	private $Title = 'Site summary';
	private $Stepping = 0;//->$_steppingMethods[]
	private $ItemCount = 15;//number of items 
	private $FilterTags = array();
	private $DisplayMethod = 'summary';
	private $FilterMethod = true; // true = whitelist | false = blacklist
	private $TitleRegex = '';
	private $DetailTemplate = '';
	private $ChannelHeaderTemplate = '';
	private $OverviewTemplate = '';
	private $ChannelFooterTemplate = '';
	private $ChangePubDateOnUpdate = NULL;
	private $DetailViewTemplate = false; //string template name | false no template
	private $AllowedProviders = array(); //list of classes asked to contribute content
	private $LastUpdate = 0;//timestamp
	private $NextUpdate = 0;//timestamp
	private $ContentProvider = array();
	//end to serialize
	private $_edited = false;
	private $_offset = 0;
	private $_displayedItems = 0;
	private $_hasMore = true;
	
	
	private $settings = array();
	
	public function __construct($settings)
	{
		//save all settings we've got
		$this->settings = $settings;
		
		//set standard channel config values
		$vars = array_keys(get_class_vars(get_class($this)));
		foreach($vars as $setting)
		{
			if(!empty($settings[$setting]) 
				&& substr($setting,0,1) != '_') //vars beginning with _ are not setable
			{
				$this->{$setting} = $settings[$setting];
			}
		}
		if($this->ID === 0)
		{
			$this->ContentProvider = get_declared_classes();
		}
	}
	
	public function __wakeup()
	{
		$vars = array_keys(get_class_vars(get_class($this)));
		foreach($vars as $setting)
		{
			if(!empty($settings[$setting]) 
				&& substr($setting,0,1) != '_') //vars beginning with _ are not setable
			{
				$this->{$setting} = $settings[$setting];
			}
		}
	}
	
	public function __get($key)
	{
		if(substr($key,-4) == '_ISO' && isset($this->settings[substr($key,0,-4)]))
		{
			return utf8_decode($this->settings[substr($key,0,-4)]);
		}
		if(isset($this->settings[$key]))
		{
			return $this->settings[$key];
		}
		$vars = array_keys(get_class_vars(get_class($this)));
		if(in_array($key, $vars) && substr($key,0,1) != '_')
		{
			return $this->{$key};
		}
		return false;
	}
	
	public function __set($key, $value)
	{
		if(substr($key,-4) == '_ISO')
		{
			$key = substr($key,0,-4);
			$value = utf8_encode($value);
		}
		switch($key)
		{
			case 'DisplayMethod':
				if(!in_array($value, array('summary', 'content', 'title', 'template')))
					return;
		}

		$vars = array_keys(get_class_vars(get_class($this)));
		if(in_array($key, $vars) && substr($key,0,1) != '_')
		{
			$this->{$key} = $value;
		}
		$this->_edited = true;
		$this->settings[$key] = $value;
	}
	
	public function settings()
	{
		return ($this->_edited) ? $this->settings : NULL;
	}	

	private function getLatestNews($offset = 0) //offset for news archive...
	{
		$sortHelp = array();
		$data = array();
		asort($sortHelp);
		$sortHelp = array_reverse(array_keys($sortHelp));
		$out = array();
		if(count($sortHelp) > $offset)
		{
			for($i = $offset; $i < ($offset+$this->ItemCount); $i++)
			{
				if(!isset($sortHelp[$i]))
				{
					$this->_hasMore = false;
					break;
				}
				$out[] = $sortHelp[$i];
			}
			if(!isset($sortHelp[$i+1]))
			{
				$this->_hasMore = false;
			}		
		}
		else
		{
			$this->_hasMore = false;
		}
		$this->_displayedItems = count($out);
		$this->_offset = $offset;
		$sortHelp = array_reverse($out);
		//array_slice($sortHelp, $offset, $this->ItemCount, true);
//		echo count($sortHelp);
//		$sortHelp = array_keys($sortHelp);
		

		$out = array();
		for($i = min(count($sortHelp)-1, $this->ItemCount); $i >= 0; $i--)
		{
			$out[] = $data[$sortHelp[$i]];
		}
		return $out;//array(array($provider, $id, $updateTime))
	}
	
	public function render($format = 'xhtml', $class = NULL, $contentid = NULL)
	{
		//TODO: for nav var and item alias query nav class
		//TODO: unique alias db for all content items
		$Linker = Linker::alloc();
		$Linker->init();
		$Configuration = Configuration::alloc();
		$Configuration->init();
		$navVar = '';
		$navSelfAlias = '';
		
		if(isset($this->settings['accessValues']))
		{
			list($navVar, $navSelfAlias) = $this->settings['accessValues'];
		}
		$temp = explode(':',$Linker->get($navVar));
		$currNav = array_shift($temp);
		if($format == 'xhtml' && $currNav != $navSelfAlias)
		{
			return NULL;
		}
		
		$pageOffset = $Linker->get('_NewsOffset');
		$pageOffset = is_numeric($pageOffset) ? max($pageOffset, 0) : 0;
		$contentArray = NULL;
		
		
		$T = Template::alloc();
		$T->init();
		$S = BCMSString::alloc();
		$S->init();
		$FS = FileSystem::alloc();
		$FS->init();
		
		//detail view on website
		if(is_array($contentArray))
		{
			$out = '';
			$tplFile = './Content/templates/'.$this->DetailTemplate;
			if(!file_exists($tplFile))
			{
				$tplFile = './System/Templates/feed_detail.tpl';
			}
			$template = $FS->read($tplFile);
			$out = $S->bsprintv($template, $contentArray);
			return $out;
		}
		//overview website or feed
		else
		{
			switch($format)
			{
				case 'rss2':
				
					$rss = RSS::alloc();
					$rss->init();
					$rss->recode = false;
					
					$rss->addTag('title', $this->Title);
					$rss->addTag('link', $Linker->myBase());
					$rss->addTag('description', (isset($this->settings['description'])) ? $this->settings['description'] : '');
					$rss->addTag('copyright', (isset($this->settings['copyright'])) ? $this->settings['copyright'] : '');
					$rss->addTag('managingEditor', (isset($this->settings['managingEditor'])) ? $this->settings['managingEditor'] : '');
					$items = $this->getLatestNews($pageOffset);
					foreach($items as $item)
					{
						list($provider, $id, $time) = $item;
						$feedEntry = $provider->getFeedItem($id);
						$cmsID = isset($feedEntry['cmsid']) ? $feedEntry['cmsid'] : get_class($provider).':'.$id;
						$guid = $Linker->myBase().'?'.$navVar.'='.$navSelfAlias.':'.$cmsID;
						$rss->newItem();
						$rss->addTag('title', $feedEntry['title']);
						$rss->addTag('guid', $guid);
						$rss->addTag('pubDate', date('r',$time));
						$rss->addTag('link', $guid);
						$desc = $feedEntry['title'];
						if(in_array($this->DisplayMethod, array('summary', 'content', 'title')))
						{
							$desc = $feedEntry[$this->DisplayMethod];
						}
						elseif($this->DisplayMethod == 'template')
						{
							$tplFile = './Content/templates/'.$this->OverviewTemplate;
							if(!file_exists($tplFile))
							{
								$tplFile = './System/Templates/feed_overview.tpl';
							}
							if(file_exists($tplFile))
							{
								$template = $FS->read($tplFile);
								$desc = $S->bsprintv($template, $feedEntry);
							}
						}
						$rss->addTag('description', $desc);
						if(!empty($feedEntry['enclosure']))
						{
							$rss->addTag('enclosure', false, array('url' => $feedEntry['enclosure']));
						}
	//					$rss->addTag('author', $feedEntry['author']);//autor needs email
					}
					return $rss->show();
	
				case 'xhtml':
				default:
					$items = $this->getLatestNews($pageOffset);
					
					//		$this->_displayedItems = count($out);
					//		$this->_offset = $offset;
					$next = ($this->_offset+15 > $this->_offset+$this->_displayedItems) ? $this->_offset : $this->_offset+15;
					$feedInfos = array(
						 'News_Next' => $Linker->createQueryString(array('_NewsOffset' => $next)) 
						,'News_Prev' => $Linker->createQueryString(array('_NewsOffset' => max(0, $this->_offset-15)))
						,'News_Displayed' => sprintf('%01d - %01d', $this->_offset+1, $this->_offset+$this->_displayedItems)
						,'News_ItemCount' => '' 
						
					);
					$out = '';
					$tplHeaderFile = './Content/templates/'.$this->ChannelHeaderTemplate;
					if(file_exists($tplHeaderFile))
					{
						$out .= $S->bsprintv($FS->read($tplHeaderFile), $feedInfos);
					}
					$tplFile = './Content/templates/'.$this->OverviewTemplate;
					if(!file_exists($tplFile))
					{
						$tplFile = './System/Templates/feed_overview.tpl';
					}
					if(file_exists($tplFile))
					{
						$template = $FS->read($tplFile);
						foreach($items as $item)
						{
							list($provider, $id, $time) = $item;
							$feedEntry = $provider->getFeedItem($id);
							$feedEntry['cmsid'] = isset($feedEntry['cmsid']) ? $feedEntry['cmsid'] : get_class($provider).':'.$id;
							$feedEntry['guid'] = $Linker->myBase().'?'.$navVar.'='.$navSelfAlias.':'.$feedEntry['cmsid'];
							$feedEntry['link'] = $Linker->createQueryString(array($navVar => $navSelfAlias.':'.$feedEntry['cmsid']));
							$out .= $S->bsprintv($template, $feedEntry);
						}			
					}
					$tplFooterFile = './Content/templates/'.$this->ChannelFooterTemplate;
					if(file_exists($tplFooterFile))
					{
						$out .= $S->bsprintv($FS->read($tplFooterFile), $feedInfos);
					}
					return $out;
			}
		}
	}
	////end feed stuff
}
##############################################################
class News extends Bambus implements IShareable
{
	//dummy vars for __get()
	private $Index = array(0 => 'Site summary');// title of special feed 0 is the sitename - 0 is a global feed 
									//id => Title;
	private $destroyCache = false; 
	private $feedSettings = array(0 => array()); //no settings - broadcast all
	private $channelStatus = array(0 => true); // true -> public 
	private $UpdateInterval = 15;//minutes
	private $supportedOutput = array('xhtml', 'rss2');
	private $openChannels = array(); //links to channels opened for editing
	private $saveData = false;
	private $dataLoaded = false;
	//destruct serializes self - bambus heritates load- and saveState methods
	//writing data to Content/config/<ClassName>.CurrentState.php
	
	public function __construct()
	{
		//load feed index
		parent::Bambus();
	}
	
	public function __destruct()
	{
		if($this->saveData)
		{
			$this->saveCurrentState();
		}
	}
	
	public function __wakeup()
	{
		parent::Bambus();
	}

	public function __sleep()
	{
		return array('Index', 'feedSettings', 'channelStatus', 'UpdateInterval');
	}
	
	public function __get($var)
	{
		switch($var)
		{
			case 'Count':
				return count($this->Index);
			case 'Index':
				$TR = Translation::alloc();
				$TR->init();
				$ret = $this->Index;
				$ret[0] = utf8_encode(html_entity_decode($TR->site_summary));  // 0 is the site global summary and is always called like the website
				return $ret;
		}
	}
	
	public function __set($var, $value)
	{
		switch($var)
		{
			case 'Count':
			case 'Index':
			default:
				return false;
		}
	}
	
	public function createChannel($title)
	{
		//create random id
		$id = md5(time().rand());
		while(isset($this->Index[$id]))
		{
			$id = md5(time().rand());
		}
		$this->Index[$id] = $title;
		$this->feedSettings[$id] = array('ID' => $id);
		$this->channelStatus[$id] = true;
		$this->saveData = true;
		return $this->openChannel($id);
	}
	
	public function removeChannel($id)
	{
		if(!empty($id))
		{
			unset($this->Index[$id]);
			unset($this->feedSettings[$id]);
			unset($this->channelStatus[$id]);
			$this->saveData = true;
		}
	}
	
	public function existsChannel($id)
	{
		return isset($this->Index[$id]);
	} 
	
	public function flushChache($channel = false)
	{
		$NotificationCenter = NotificationCenter::alloc();
		$NotificationCenter->init();
		//remove all News_* from temp
		$FS = FileSystem::alloc();
		$FS->init();
		$files = $FS->queryPath('temp', 'News_', true);
		foreach($files as $file)
		{
			unlink($file);
		}
		$NotificationCenter->report('information', 'feed_cache_will_be_updated', array('class' => get_class($this)));
	}
	
	public function renameChannel($id, $newName)
	{
		if(isset($this->Index[$id]) && $id != 0)
		{
			$this->Index[$id] = $newName;
			$this->feedSettings[$id]['Title']  = $newName;
			$this->flushChache($id);
			$this->saveData = true;
		}
	}
	
	public function openChannel($id)
	{
		//$openChannels += $id=>$object
		if(isset($this->Index[$id]))
		{
			$TR = Translation::alloc();
			$TR->init();
			$ret = $this->Index;
			$ret[0] = utf8_encode(html_entity_decode($TR->site_summary));
			$this->feedSettings[$id]['Title'] = $ret[$id];
			$this->openChannels[$id] = new News_Channel($this->feedSettings[$id]);
			return $this->openChannels[$id];
		}	
		return NULL;
	}
	
	public function closeChannel($id)
	{
		//save $openChannels[$id] and destroy it
		if(isset($this->openChannels[$id]) && isset($this->Index[$id]) && $this->openChannels[$id]->settings() != NULL)
		{
			$this->feedSettings[$id] = $this->openChannels[$id]->settings();
			$this->Index[$id] = (!empty($this->feedSettings[$id]['Title'])) 
				? $this->feedSettings[$id]['Title'] 
				: $this->Index[$id];
			$this->openChannels[$id] = NULL;
			unset($this->openChannels[$id]);
			$this->flushChache($id);
			$this->saveData = true;
			return true;
		}
		return false;
	}
	
	
	public function render($channel = -1, $as = 'xhtml', $force = false, $class = NULL, $contentid = NULL)
	{
		$NotificationCenter = NotificationCenter::alloc();
		$NotificationCenter->init();
		$NotificationCenter->report('information', 'feed_cache_updated', array('class' => get_class($this)));
		
		if(!in_array($as, $this->supportedOutput))
			return false;
			
		$FS = FileSystem::alloc();
		$FS->init();
		if($this->existsChannel($channel))
		{
			//use cache of feed or render new and cache it
			$Linker = Linker::alloc();
			$Linker->init();
			$pageOffset = $Linker->get('_NewsOffset');
			$pageOffset = is_numeric($pageOffset) ? max($pageOffset, 0) : 0;
			$cacheFile = parent::pathTo('temp').'News_'.$channel.'_'.$pageOffset.'.'.$as;
			
			
			if((($class != NULL && $contentid != NULL) 
				|| $force 
				|| !file_exists($cacheFile) 
				|| !is_readable($cacheFile) 
				|| time() > (filemtime($cacheFile) + $this->UpdateInterval*60))
			){
				//init feed and let it gather items
				$c = new News_Channel($this->feedSettings[$channel]);
				$content = $c->render($as, $class, $contentid);
				//render new cache file
				
				if($content != NULL && $class == NULL && $contentid == NULL)
				{
					$FS->writeData($cacheFile, $content, false);
//		
				}
				return $content;
			}
			else
			{
				return $FS->readData($cacheFile);
			}
		}
	}
	
	public function getChannelByAlias($alias)
	{
		foreach($this->feedSettings as $channel => $settings)
		{
			if(isset($settings['accessValues']) 
				&& isset($settings['accessValues'][1])
				&& $settings['accessValues'][1] == $alias
				)
			{
				return $channel;
			}
		}
		return false;
	}
	
	public function accessValuesChanged($channel, $navigationKey, $objectAlias)
	{
		$NotificationCenter = NotificationCenter::alloc();
		$NotificationCenter->init();
		$NotificationCenter->report('information', 'updating_feed_access_variables ', array());
		if(isset($this->feedSettings[$channel]))
		{
			$this->feedSettings[$channel]['accessValues'] = array($navigationKey, $objectAlias);
			$this->saveData = true;
		}
	}
	
	//IShareable
	const Class_Name = 'News';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			//if config file exists $sharedInstance = unserialized(cfg)
			$lastState = parent::loadPreviousState($class);
			if($lastState != NULL && is_object($lastState) && get_class($lastState) == $class)
			{
				self::$sharedInstance = $lastState;
			}
			else
			{
				self::$sharedInstance = new $class();
				$NotificationCenter = NotificationCenter::alloc();
				$NotificationCenter->init();
				$NotificationCenter->report('warning', 'news_state_not_restored', array());
			}
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
			
    	}
    }
	//end IShareable
}

?>