<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 05.03.2008
 * @license GNU General Public License 3
 */
class STag extends BSystem implements IShareable, IUseSQLite,
	HContentChangedEventHandler, HContentCreatedEventHandler,
	HContentDeletedEventHandler 
{
	/**
	 * @param EContentChangedEvent $e
	 */
	public function HandleContentChangedEvent(EContentChangedEvent $e)
	{
		$this->update($e->Content);
	}
	
	/**
	 * @param EContentCreatedEvent $e
	 */
	public function HandleContentCreatedEvent(EContentCreatedEvent $e)
	{
		$this->update($e->Content);
	}
	
	/**
	 * @param EContentDeletedEvent $e
	 */
	public function HandleContentDeletedEvent(EContentDeletedEvent $e)
	{
		$this->set($e->Content, '');
	}
	
	//IShareable
	const Class_Name = 'STag';
	/**
	 * @var SContentIndex
	 */
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @var SQLiteDatabase
	 */
	private static $DB = null;
	/**
	 * @return STag
	 */
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
			self::$DB = DSQLite::alloc()->init();
		}
		return self::$sharedInstance;
	}
    
	/**
	 * @return STag
	 */
    function init()
    {
    	return $this;
    }
	//end IShareable
	
	private static $_managers = null;
	
	/**
	 * Uniform way to convert a string with a bunch of tags in a useful array
	 *
	 * @param string $tagstr
	 */
	public static function parseTagStr($tagstr)
	{
		$tagstr = preg_replace("/[\\r\\n\\t,;\\s]+/u", ";", trim($tagstr));
		$tags = explode(';', $tagstr);
		$tagarr = array();
		foreach ($tags as $tag) 
		{
			if(ctype_space($tag) || $tag == "") 
			{
				continue;
			}
			$tagarr[] =  $tag;
		}
		$tagarr = array_unique($tagarr);
		return $tagarr;
	}
	
	private function setTags($managerId, $contentID, $tagstring)
	{
		$tags = self::parseTagStr($tagstring);
		$nfc = NotificationCenter::alloc();
		$nfc->init();
		
		self::$DB->queryExec("BEGIN TRANSACTION;");
		//remove all rels to cid
		$sql = sprintf("DELETE FROM relContentTags WHERE contentREL = "
			."(SELECT contentID FROM ContentIndex WHERE managerContentID = '%s' ".
			"and managerREL = (SELECT managerID from Managers WHERE manager LIKE '%s'))"
			, sqlite_escape_string($contentID)
			, sqlite_escape_string($managerId));
		self::$DB->queryExec($sql, $err);
		foreach ($tags as $tag) 
		{
			//insert ignore tag
			$sql = sprintf("INSERT OR IGNORE INTO Tags (tag)VALUES('%s')"
				,sqlite_escape_string($tag));
			self::$DB->queryExec($sql, $err);
			$sql = sprintf("INSERT INTO relContentTags (contentREL, tagREL)VALUES(
					(SELECT contentID FROM ContentIndex WHERE managerContentID = '%s' ".
					"and managerREL = (SELECT managerID from Managers WHERE manager LIKE '%s')),
					(SELECT tagID FROM Tags WHERE tag LIKE '%s')
				)"
				,sqlite_escape_string($contentID)
				,sqlite_escape_string($managerId)
				,sqlite_escape_string($tag)
			);
			self::$DB->queryExec($sql, $err);
		}
		self::$DB->queryExec("COMMIT;");// ? 'committed' : 'argh';
		
	}
	
	private function getTags($managerId, $contentId)
	{
		$sql = sprintf("SELECT Tags.tag FROM Tags ".
			"LEFT JOIN relContentTags ON (relContentTags.tagREL = Tags.tagID) ".
			"LEFT JOIN ContentIndex ON (relContentTags.contentREL = ContentIndex.contentID) ".
			"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
			"WHERE ContentIndex.managerContentID LIKE '%s' and Managers.manager LIKE '%s' ORDER BY Tags.tag;"
			, sqlite_escape_string($contentId)
			, sqlite_escape_string($managerId)
		);
		$tagres = self::$DB->query($sql);

		$tags = array();
		while($tag = $tagres->fetch(SQLITE_NUM))
		{
			$tags[] = $tag[0];
		}
		return $tags;
	}
	
//	private function resolveManager($manager)
//	{
//		if ($manager instanceof BContentManager ) 
//		{
//			$managerstr = get_class($manager);
//		}
//		else
//		{
//			$managerstr = $manager;
//		}
//		if(self::$_managers == null)
//		{
//			self::$_managers = array();
//			$res = self::$DB->query("SELECT * FROM Managers WHERE 1;");
//			while($arr = $res->fetch())
//			{
//				self::$_managers[$arr[1]] = $arr[0];
//			}
//		}
//		return isset($_managers[$managerstr]) ? $_managers[$managerstr] : null;
//	}
	
	/**
	 * Assign tags to a content-element defined by its controller and its id
	 *
	 * @param BContent $content
	 */
	public function update(BContent $content)
	{
		$this->setTags($content->getManagerName(), $content->Id, implode(',', $content->Tags));
	}
	
	/**
	 * Assign tags to a content-element defined by its controller and its id
	 *
	 * @param BContent $content
	 * @param string $tagstr
	 */
	public function set(BContent $content, $tagstr)
	{
		$this->setTags($content->getManagerName(), $content->Id, $tagstr);
	}
	
	/**
	 * Get all tags assigned to a content-element defined by its manager and its id
	 *
	 * @param BContent $content
	 * @return array
	 */
	public function get(BContent $content)
	{
		return $this->getTags($content->getManagerName(), $content->Id);
	}
	
	/**
	 * Count the usage of each tag in $tagstring 
	 *
	 * @param string $tagstr
	 */
	public function count($tagstr)
	{
		
	}
	
	/**
	 * All elements having all tags in the $tagstring
	 *
	 * @param string $tagstr
	 */
	public function having($tagstr)
	{
		
	}
	
	/**
	 * All elements having one or more tags from $tagstring
	 *
	 * @param string $tagstr
	 */
	public function any($tagstr)
	{
		
	}
	
	/**
	 * All elements having only and exactly the elements in $tagstring
	 *
	 * @param string $tagstr
	 */
	public function exact($tagstr)
	{
		
	}
	
	/**
	 * Generate an assoc array containg either the most used tags or the tags in $tagstring as key and their usage as value. 
	 * Item count is limited by $limit. $limit = 0 means NO limit.
	 *
	 * @param int $limit
	 * @param string $tagstr
	 */
	public function cloud($limit = 0, $tagstr = null)
	{
		
	}
	
	/**
	 * Generate an assoc array with all elements beginning with $tag ordered by their usage
	 *
	 * @param string $tag
	 */
	public function complete($tag)
	{
		
	}
	
	/**
	 * return all tags and their usage as assoc array
	 *
	 */
	public function all()
	{
		
	}
	
	/**
	 * return all blocked tags
	 *
	 */
	public function blocked()
	{
		
	}
	
	/**
	 * block all tags in $tagstring
	 *
	 * @param string $tagstr
	 */
	public function block($tagstr)
	{
		
	}
	
	/**
	 * remove block from all tags in $tagstring
	 *
	 * @param string $tagstr
	 */
	public function  unblock($tagstr)
	{
		
	}
}
?>