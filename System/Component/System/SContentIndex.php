<?php
/**
 * Indexes Manager, Id, Title, pubDate, changeDate and Text(first 1024 chars) of content objects
 *
 */
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 24.03.2008
 * @license GNU General Public License 3
 */
class SContentIndex 
	extends BSystem 
	implements 	IShareable, IUseSQLite,
				HContentChangedEventHandler, HContentCreatedEventHandler, HContentDeletedEventHandler,
				HContentPublishedEventHandler, HContentRevokedEventHandler, HContentAccessEventHandler 
{
	//IShareable
	const Class_Name = 'SContentIndex';
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
	 * @return SContentIndex
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
	 * @return SContentIndex
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
	
	/**
	 * @param string $query
	 * @param boolean $needsAll
	 * @param int $items
	 * @param int $offset
	 * @param string $ofManger
	 * @param string $tagged
	 * @return string
	 * @throws Exception 
	 */
	private function buildSQLForSearch($query, $needsAll, $items, $offset, $ofManger, $tagged)
	{
		//Define what we want
		$sql = 	"SELECT ContentIndex.title AS Title, ContentIndex.pubDate AS PubDate, Managers.manager AS Manager,".
				"ContentIndex.managerContentID AS ContentID, ContentIndex.summary AS Summary ".
				"FROM ContentIndex LEFT JOIN Managers ".
				"ON (ContentIndex.managerREL = Managers.managerID) ";  
		//initialize tags
		$tags = STag::parseTagStr($tagged);
		$querystr = preg_replace("/[\\r\\n\\t,;\s]+/u", ";", $query);
		$querywords = explode(';', $querystr);
		$querywords = array_unique($querywords);
		
		//filter by manager
		$sql .= ($ofManger == null) ? "WHERE 1 " : "WHERE Managers.manager LIKE '".sqlite_escape_string($ofManger)."' ";
		$concat = $needsAll ? ' AND ' : ' OR ';
		$qrys = array();
		foreach ($querywords as $word) 
		{
			if(strlen($word) > 2)
			{
				$qrys[] = "ContentIndex.summary LIKE '%".sqlite_escape_string($word)."%'";
			}
		}
		if(count($qrys) > 0)
		{
			$sql .= ' AND ('.implode($concat, $qrys).') ';
		}
		else
		{
			throw new Exception("query too unspecific");
		}
		//filter by tag
		if(is_array($tags) && count($tags) > 0)
		{
			foreach ($tags as $tag) 
			{
				$sql .= "AND ContentIndex.contentID IN (SELECT relContentTags.contentREL FROM relContentTags LEFT JOIN Tags ON (Tags.tagID = relContentTags.tagREL) ".
					"WHERE Tags.tag LIKE '".sqlite_escape_string($tag)."') ";
			}
		}
		
		//order 
		$sql .= "ORDER BY ContentIndex.pubDate DESC ";
		
		//limit and number of items - no limit -> offset is useless
		if(is_numeric($items) && $items > 0)
		{
			//offset,limit/limit
			$sql .= ($offset > 0 && is_numeric($offset)) ? "LIMIT ".$offset.",".$items." " : "LIMIT ".$items." ";
		}
		return $sql;
	}
	
	/**
	 * Search in content index
	 * @param string $query
	 * @param boolean $needsAll
	 * @param int $items
	 * @param int $offset
	 * @param string $ofManger
	 * @param string $tagged
	 * @return array
	 * @throws Exception
	 */
	public function search($query, $needsAll = true, $items = 0, $offset = 0, $ofManger = null, $tagged = "", $fetchAssoc = false)
	{
		$sql = $this->buildSQLForSearch($query, $needsAll, $items, $offset, $ofManger, $tagged);
		$res = self::$DB->query($sql, $err);
		$return = array();
		while($data = $res->fetch($fetchAssoc ? SQLITE_ASSOC : SQLITE_NUM))
		{
			$return[] = $data;
		}
		return $return;
	}
	
	public static function isPublic($managerClass,  $contentId, $checkPubDate = true)
	{
		self::alloc()->init();
		$sql = "SELECT ContentIndex.pubDate FROM ContentIndex LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
		"WHERE Managers.manager LIKE '".sqlite_escape_string($managerClass)."' ".
		"AND ContentIndex.managerContentID LIKE '".sqlite_escape_string($contentId)."' ".
		(($checkPubDate) ? "AND ContentIndex.pubDate > 0 AND ContentIndex.pubDate <= ".time() : '');
		$res = self::$DB->query($sql);
		return $res->numRows() > 0;
	}

	public static function getContentInformationBulk(array $cmsids)
	{
		self::alloc()->init();
		$bulk = array();
		$result = array();
		foreach ($cmsids as $id) 
		{
			if(strpos($id,':'))
			{
				$split = explode(':',$id);
				if(count($split) != 2)
				{
					continue;
				}
				$bulk[] = " (ContentIndex.managerContentId LIKE '".sqlite_escape_string($split[1]).
							"' AND Managers.manager LIKE '".sqlite_escape_string($split[0])."') ";
			}
		}
		if(count($bulk))
		{
			$connection = DSQLite::alloc()->init();
			$sql = "SELECT DISTINCT ContentIndex.managerContentID AS CID, Managers.manager AS Manager, ".
					"ContentIndex.title as Title, Aliases.alias AS Alias, ContentIndex.pubDate AS PubDate ".
					"FROM Aliases ".
					"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
					"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
					"WHERE Aliases.active = 1 AND (".
						implode('OR', $bulk).
					")";
			$res = $connection->query($sql, SQLITE_ASSOC);
			if($res->numRows() > 0)
			{
				while ($erg = $res->fetch())
				{
					$result[$erg['Manager'].':'.$erg['CID']] = array(
						'Title' => $erg['Title'], 
						'Alias' => $erg['Alias'], 
						'PubDate' => $erg['PubDate']
					);
				}
			}
		}
		return $result;
	}
	
	public static function getTitleAndAlias($manager, $contentId)
	{
		self::alloc()->init();
		$connection = DSQLite::alloc()->init();
		$sql = "SELECT ContentIndex.title as Title, Aliases.alias AS Alias, ContentIndex.pubDate AS PubDate ".
					"FROM Aliases ".
					"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
					"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
					"WHERE ContentIndex.managerContentId LIKE '".sqlite_escape_string($contentId)."' ".
					"AND Managers.manager LIKE '".sqlite_escape_string($manager)."' ".
					"AND Aliases.active = 1"
		;
		$res = $connection->query($sql, SQLITE_ASSOC);
		if($res->numRows() > 0)
		{
			return $res->fetch();
		}
		
		$simpleAlias = $manager.':'.$contentId;
		
		$sci = SComponentIndex::alloc()->init();
		if($sci->IsExtension($manager, 'BContentManager'))
		{
			$man = new $manager();
			$man = $man->alloc()->init();
			$cnt = $man->Open($contentId);
			return array(
				'Title' => $cnt->Title, 
				'Alias' => $simpleAlias,
				'PubDate' => $cnt->PubDate
			);
		}
		return array('','');
	}	
	//@todo getNext
	//@todo getPrevious
	
	private function buildSQLForCount($ofManger, $tagged)
	{
		//Define what we want
		$sql = 	"SELECT COUNT(ContentIndex.title) AS Count FROM ContentIndex LEFT JOIN Managers ".
				"ON (ContentIndex.managerREL = Managers.managerID) ";  
		
		//initialize tags
		$tags = STag::parseTagStr($tagged);
		
		//filter by manager
		$sql .= ($ofManger == null) ? "WHERE 1 " : "WHERE Managers.manager LIKE '".sqlite_escape_string($ofManger)."' ";
		
		//filter by tag
		if(is_array($tags) && count($tags) > 0)
		{
			foreach ($tags as $tag) 
			{
				$sql .= "AND ContentIndex.contentID IN (SELECT relContentTags.contentREL FROM relContentTags LEFT JOIN Tags ON (Tags.tagID = relContentTags.tagREL) ".
					"WHERE Tags.tag LIKE '".sqlite_escape_string($tag)."') ";
			}
		}
		return $sql;
	}
	
	private function buildSQLForList($items, $offset, $ofManger, $tagged, $latestFirst)
	{
		//Define what we want
		$sql = 	"SELECT ContentIndex.title AS Title, ContentIndex.pubDate AS PubDate, Managers.manager AS Manager,ContentIndex.managerContentID AS ContentID FROM ContentIndex LEFT JOIN Managers ".
				"ON (ContentIndex.managerREL = Managers.managerID) ";  
		
		//initialize tags
		$tags = STag::parseTagStr($tagged);
		
		//filter by manager
		$sql .= ($ofManger == null) ? "WHERE 1 " : "WHERE Managers.manager LIKE '".sqlite_escape_string($ofManger)."' ";
		
		//filter pubDate
		$sql .= "AND ContentIndex.pubDate > 0 AND ContentIndex.pubDate < ".time()." ";
		
		//filter by tag
		if(is_array($tags) && count($tags) > 0)
		{
			foreach ($tags as $tag) 
			{
				$sql .= "AND ContentIndex.contentID IN (SELECT relContentTags.contentREL FROM relContentTags LEFT JOIN Tags ON (Tags.tagID = relContentTags.tagREL) ".
					"WHERE Tags.tag LIKE '".sqlite_escape_string($tag)."') ";
			}
		}
		
		//order 
		$sql .= "ORDER BY ContentIndex.pubDate ".(($latestFirst) ? "DESC " : "ASC ");
		
		//limit and number of items - no limit -> offset is useless
		if($items > 0 && is_numeric($items))
		{
			//offset,limit/limit
			$sql .= ($offset > 0 && is_numeric($offset)) ? "LIMIT ".$offset.",".$items." " : "LIMIT ".$items." ";
		}
		return $sql;
	}
	
	public function getLatest($items = 0, $offset = 0, $ofManger = null, $tagged = "", $latestFirst = true, $fetchAssoc = false)
	{
		$sql = $this->buildSQLForList($items, $offset, $ofManger, $tagged, $latestFirst);
		$res = self::$DB->query($sql);
		$return = array();
		while($data = $res->fetch($fetchAssoc ? SQLITE_ASSOC : SQLITE_NUM))
		{
			$return[] = $data;
		}
		return $return;
	}
	
	public function countLatest($ofManger = null, $tagged = "")
	{
		$sql = $this->buildSQLForCount($ofManger, $tagged);
		$res = self::$DB->query($sql, $err);
		$return = array();
		$data = $res->fetch(SQLITE_NUM);
		return $data[0];
	}
	
	public function countSearch($ofManger = null, $tagged = "")
	{
		throw new Exception('not implemented');
		//@todo implement
	}
	
	/**
	 * Insert manager into manager db if it does not exist and always return the db id
	 *
	 * @param string $manager
	 * @return int
	 */
	private function getManagerId($manager)
	{
		self::$DB->queryExec("INSERT OR IGNORE INTO Managers (manager) VALUES ('".sqlite_escape_string($manager)."');");
		$res = self::$DB->query("SELECT managerID FROM Managers WHERE manager LIKE '".sqlite_escape_string($manager)."';");
		$arr = $res->fetch(SQLITE_NUM);
		return (!empty($arr)) ? $arr[0] : null;
	}
	
	/**
	 * Update content index
	 *
	 * @param BContent $content
	 */
	private function updateIndex(BContent $content)
	{
		$manager = $content->getManagerName();
		$managerID = $this->getManagerId($manager);
		$contentID = sqlite_escape_string($content->Id);
		$result = self::$DB->query("SELECT COUNT(managerContentID) FROM ContentIndex WHERE managerContentID = '".$contentID."' AND managerREL = ".$managerID);
		$dat = $result->fetch();
		if($dat[0] == 0)
		{
			//insert new data 
			self::$DB->queryExec(sprintf("INSERT INTO ContentIndex ".
				"(managerREL, managerContentID, title, pubDate, summary)".
				"VALUES('%d','%s', '%s',%d,'%s');"
				,$managerID
				,$contentID
				,sqlite_escape_string($content->Title)
				,$content->PubDate
				,sqlite_escape_string(mb_substr(preg_replace("/[\\s]+/u"," ",$content->Text),0,1024, 'UTF-8'))
				), $err
			);
		}
		else
		{
			//update data
			self::$DB->queryExec(sprintf("UPDATE ContentIndex SET title='%s', pubDate=%d, summary='%s' ".
									"WHERE managerContentID LIKE '%s' AND managerREL = %d;"
				,sqlite_escape_string($content->Title)
				,$content->PubDate
				,sqlite_escape_string(mb_substr(preg_replace("/[\\s]+/u"," ",$content->Text),0,1024))
				,$contentID
				,$managerID
			));
		}
		//update change log
		self::$DB->queryExec(sprintf("INSERT INTO Changes (contentREL, title, size, changeDate, username)".
								"VALUES((SELECT contentID FROM ContentIndex WHERE managerContentID = '%s' AND managerREL = %d), ".
								"'%s', '%d', '%d', '%s');"
			,$contentID
			,$managerID
			,sqlite_escape_string($content->Title)
			,isset($content->Size) ? $content->Size : 0 //mb_strlen($content->Content, 'UTF-8')
			,time()
			,sqlite_escape_string(BAMBUS_USER)
		));
		
		//FIXME register event handler in salias
//		SAlias::alloc()->init()->updateAlias($content);
		//FiXME register event handlers in stag
	}
	
	public function getMeta(BContent $content)
	{
		//get assoc array:title,pubDate,createDate,modDate,createdBy,modifiedBy,size
		
		$manager = $content->getManagerName();
		$cid = $content->Id;
		$meta = array();
		//@todo optimize sql
		$sql = "SELECT ContentIndex.title, ContentIndex.pubDate, ContentIndex.contentID FROM ContentIndex LEFT JOIN ".
					"Managers ON (ContentIndex.managerREL = Managers.managerID) ".
					"WHERE managerContentID LIKE '".sqlite_escape_string($cid)."' ".
					"AND Managers.manager LIKE '".sqlite_escape_string($manager)."' LIMIT 1";
		$res = self::$DB->query($sql);
		$arr = $res->fetch(SQLITE_NUM);
		$meta['Title'] = array_shift($arr);
		$meta['PubDate'] = array_shift($arr);
		$dbid = array_shift($arr);
		
		$sql = "SELECT size, changeDate, username FROM Changes WHERE (".
				"changeDate = (SELECT changeDate FROM Changes WHERE contentREL=".sqlite_escape_string($dbid)." ORDER BY changeDate ASC LIMIT 1) OR ".
				"changeDate = (SELECT changeDate FROM Changes WHERE contentREL=".sqlite_escape_string($dbid)." ORDER BY changeDate DESC LIMIT 1)".
			") AND contentREL=".sqlite_escape_string($dbid);
		$res = self::$DB->query($sql);
		
		$arr = $res->fetch(SQLITE_NUM);
		list($meta['Size'], $meta['CreateDate'], $meta['CreatedBy']) = $arr;
		$arr = $res->fetch(SQLITE_NUM);
		list($meta['Size'], $meta['ModifyDate'], $meta['ModifiedBy']) = $arr;
		
		return $meta;
	}
	
	public function getIndex(BContentManager $manager)
	{
		$sql = "SELECT ContentIndex.managerContentId, ContentIndex.Title FROM ContentIndex ".
				"LEFT JOIN Managers ON(ContentIndex.managerREL = Managers.managerID) ".
				"WHERE ContentIndex.pubDate > -1 AND Managers.manager LIKE '".sqlite_escape_string(get_class($manager)).
				"' ORDER BY ContentIndex.Title ASC";
		$res = self::$DB->query($sql);
		$index = array();
		while ($arr = $res->fetch(SQLITE_NUM))
		{
			$index[$arr[0]] = $arr[1];
		}
		return $index;
	}
	
	/**
	 * Remove from database
	 *
	 * @param BContent $content
	 */
	private function removeFromIndex(BContent $content)
	{
		//do update stuff
		$manager = $content->getManagerName();
		$managerID = $this->getManagerId($manager);
		$contentID = $content->Id;
		//set index data to null
		self::$DB->queryExec(sprintf("UPDATE ContentIndex SET title='', pubDate=-1, summary='' WHERE managerContentID LIKE '%s' AND managerREL = %d;"
			,$contentID
			,$managerID
		));
		//update log
		self::$DB->queryExec(sprintf("INSERT INTO Changes (contentREL, title, size, changeDate, username)".
								"VALUES((SELECT contentID FROM ContentIndex WHERE managerContentID = '%s' AND managerREL = %d), '', -1, '%d', '%s');"
			,$contentID
			,-1
			,time()
			,sqlite_escape_string(BAMBUS_USER."@".$_SERVER['REMOTE_ADDR'])
		));
		SAlias::alloc()->init()->removeAliases($content);
	}
	
	/**
	 * @param EContentChangedEvent $e
	 */
	public function HandleContentChangedEvent(EContentChangedEvent $e)
	{
		$this->updateIndex($e->Content);
	}
	
	/**
	 * @param EContentCreatedEvent $e
	 */
	public function HandleContentCreatedEvent(EContentCreatedEvent $e)
	{
		$this->updateIndex($e->Content);
	}
	
	/**
	 * @param EContentDeletedEvent $e
	 */
	public function HandleContentDeletedEvent(EContentDeletedEvent $e)
	{
		$this->removeFromIndex($e->Content);
	}

	/**
	 * @param EContentPublishedEvent $e
	 */
	public function HandleContentPublishedEvent(EContentPublishedEvent $e)
	{
		$this->updateIndex($e->Content);
	}
	
	/**
	 * @param EContentAccessEvent $e
	 */
	public function HandleContentAccessEvent(EContentAccessEvent $e)
	{
		$pubDate = $e->Content->PubDate;
		if(empty($pubDate) || $pubDate > time())
		{
			$e->Cancel();
		}
	}
	
	/**
	 * @param EContentRevokedEvent $e
	 */
	public function HandleContentRevokedEvent(EContentRevokedEvent  $e)
	{
		$this->updateIndex($e->Content);
	}
}
?>