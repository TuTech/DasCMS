<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 16.08.2007
 * @license GNU General Public License 3
 */
class Pages extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'Pages';
	
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
			$this->Configuration = Configuration::alloc();
			$this->FileSystem = FileSystem::alloc();
			$this->NotificationCenter = NotificationCenter::alloc();

			$this->Configuration->init();
			$this->FileSystem->init();
			$this->NotificationCenter->init();

	    	//read index here - if you dont want to waste memory, just don't call me
	        $file = parent::pathToFile('documentIndex');
	        $this->pageIndex = $this->FileSystem->readData($file, true);
	        $this->pageMeta = $this->FileSystem->readData(dirname($file).'/meta.php', true);
    	}
    }
	//end IShareable
	
	
	private $pageIndex = array();
	private $pageMeta = array();
	private $bdfstring = '<?php /*BambusDocumentFile1*/ if(!class_exists("Bambus"))exit();?>';
	private $fake = false;
	
	//dummy vars 
	private $Ids, $Names, $Index, $Count;
	
	function __construct()
	{
		parent::Bambus();
		require_once(substr(__FILE__,0,-4).'/'.self::Class_Name.'_Page.php');
	}
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	

	public function __get($var)
	{
		switch($var)
		{
			case 'Ids':
				return array_keys($this->pageIndex);
			case 'Names':
				return array_values($this->pageIndex);
			case 'Index':
				return $this->pageIndex;
			case 'Count':
				return count($this->pageIndex);
		}
		if(isset($this->pageIndex[$var]))
		{
			return $this->open($var);
		}
		return NULL;
	}
	
	public function __isset($var)
	{
		return (in_array($var, array('Ids', 'Names', 'Index', 'Count')) || isset($this->pageIndex[$var]));
	}
	
	public function __set($var, $value)
	{
		//properties are read only
	}
	
	public function exists($id)
	{
		return isset($this->pageIndex[$id]);
	}
	
	public function open($id, $initWithoutContent = false)
	{
		$returnObject = NULL;
		//create new page object, fill it with data and return it
		if(isset($this->pageIndex[$id]) && isset($this->pageMeta[$id]))
		{
			$content = NULL;
			if(!$this->fake && !$initWithoutContent)
			{
				$path = parent::pathTo('document');
				$file = $path.$id.'.php';
				$content = $this->FileSystem->read($file, true);
				if(substr($content,0,strlen($this->bdfstring)) == $this->bdfstring)
				{
					$content = substr($content,strlen($this->bdfstring));
				}
			}
			//Page:: __construct($id, $title = false, $content = '', $type = 'HTML', $meta = array())
			$returnObject = new Pages_Page($id, $this->pageIndex[$id], $content, $this->pageMeta[$id]['type'], $this->pageMeta[$id]);
		}
		return $returnObject;
	}
	
	public function save(Pages_Page $pageObject)
	{
		//receive page object and write its data to the appropriate files
		$idToSave = $pageObject->Id;
		$path = parent::pathTo('document');
		$creat = false;
		if(empty($pageObject->Id))
		{
			//create new id
			$entropy = md5(date('r').time().rand().rand());
			while(isset($this->pageIndex[$entropy]))
			{
				$entropy = md5(date('r').time().rand().rand());
			}
			$this->pageIndex[$entropy] = 'new page '.date('r');
			$idToSave = $entropy;
			if(!is_writable($path))
			{
				$this->NotificationCenter->report('alert', 'no_write_permission_in_document_path', array('class' => get_class($this)));
				return false;
			}
			else
			{
				$creat = true;
			}
		}
		
		//save file first
		if($pageObject->Modified)
		{
			$file = $path.$idToSave.'.php';
			if(!$this->FileSystem->write($file, $this->bdfstring.$pageObject->Content))
			{
				$this->NotificationCenter->report('alert', 'no_write_permission_for_document', array('document' => $idToSave, 'class' => get_class($this)));
				return false;
			}
		}
		//update index
		if($this->pageIndex[$idToSave] != $pageObject->Title)
		{
			$file = parent::pathToFile('documentIndex');
			$this->pageIndex[$idToSave] = $pageObject->Title;
			if(!$this->FileSystem->writeData($file, $this->pageIndex))
			{
				$this->NotificationCenter->report('alert', 'no_write_permission_for_document_index_file', array('document' => $idToSave, 'class' => get_class($this)));
				return false;
			}
		}
		//update meta
		if($pageObject->MetaUpdated)
		{
			$file = parent::pathToFile('documentIndex');
			$file = dirname($file).'/meta.php';
			$this->pageMeta[$idToSave] = $pageObject->Meta;
			$this->pageMeta[$idToSave]['type'] = $pageObject->Type;
			if(!$this->FileSystem->writeData($file, $this->pageMeta))
			{
				$this->NotificationCenter->report('alert', 'no_write_permission_for_document_meta_file', array('document' => $idToSave, 'class' => get_class($this)));
				return false;
			}
			
		}
		//new feed update mechanism
		{
			$super = MPageManager::alloc();
			$super->init();
			$page = null;
			if(!$super->Exists($idToSave))
			{
				//create page
				$page = $super->Create($pageObject->Title, $idToSave);
				$page->CreateDate = $pageObject->Meta['creationtime'];
			}
			else
			{
				//open page
				$page = $super->Open($idToSave);
			}
			{
				//dump data to new page obj and save
				$page->Title = $pageObject->Title;
				$page->PubDate = $pageObject->publish;
				$page->ModifyDate = $pageObject->Meta['modificationtime'];
				$page->ModifiedBy = $pageObject->Meta['modifier'];
				if($pageObject->Type == 'HTML')
				{
					$page->Content = $pageObject->Content;
				}
				
				if($pageObject->MetaUpdated)
				{
					$page->Tags = $pageObject->Tags;
				}
				//save
				$page->Save();
			}
		}
		
		$this->updateFeedIndex();
		return $idToSave;
		//return successfull write
	}
	

	public function create($pageName, $content = '', $type = 'HTML')
	{
		//create new page object ann fill it with some defaults
		//Page:: __construct($id, $title = false, $content = '', $type = 'HTML', $meta = array())
		return new Pages_Page(0, $pageName, $content, $type);
	}
	
	public function delete(Pages_Page $pageObject)
	{
		//delete page identified by object
		return $this->deleteId($pageObject->Id);
	}
	
	public function deleteId($id)
	{
		//delate page by id
		$return = false;
		if(!empty($id) && isset($this->pageIndex[$id]))
		{
			//remove from indices
			$pageTitleForEvent = $this->pageIndex[$id];
			unset($this->pageIndex[$id]);
			unset($this->pageMeta[$id]);
			
			//delete content file
			$path = parent::pathTo('document');
			$file = $path.$id.'.php';
			if(!@unlink($file))
			{
				$this->NotificationCenter->report('alert', 'could_not_delete_file', array('file' => $file, 'class' => get_class($this)));
			}
			
			//save file index
			$file = parent::pathToFile('documentIndex');
			if(!$this->FileSystem->writeData($file, $this->pageIndex))
			{
				$this->NotificationCenter->report('alert', 'no_write_permission_for_document_index_file', array('class' => get_class($this)));
			}
			
			//save file meta index
			$file = dirname($file).'/meta.php';
			if(!$this->FileSystem->writeData($file, $this->pageMeta))
			{
				$this->NotificationCenter->report('alert', 'no_write_permission_for_document_meta_file', array('class' => get_class($this)));
			}
			$return = true;
		}
		return $return;
	}
	
	//template functions 
	public function embed($id)
	{
		$content = '';
		$par = $id;
		if(is_array($id) && count($id) >= 1)
		{
			$id = array_shift($id);
			if(empty($id))
			{
				$id = array_shift(array_keys($par));
			}
		}
		if(isset($this->pageIndex[$id]))
		{
			$path = parent::pathTo('document');
			$file = $path.$id.'.php';
			$content = $this->FileSystem->read($file, true);
			if(substr($content,0,strlen($this->bdfstring)) == $this->bdfstring)
			{
				$content = substr($content,strlen($this->bdfstring));
			}
		}
		return $content;		
	}
	
	public function title($id)
	{
		$par = $id;
		if(is_array($id) && count($id) >= 1)
		{
			$id = array_shift($id);
			if(empty($id))
			{
				$id = array_shift(array_keys($par));
			}
		}
		return (isset($this->pageIndex[$id])) ? $this->pageIndex[$id] : '';
	}
	
	public function allowCallFromTemplate($function)
	{
		$granted = (in_array($function, array('title', 'embed')) || isset($this->pageIndex[$function]));
		return $granted;
	}
	//end template functions	
}
?>