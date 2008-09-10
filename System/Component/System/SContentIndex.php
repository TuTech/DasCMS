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
	implements 	IShareable, 
				HContentChangedEventHandler, HContentCreatedEventHandler, HContentDeletedEventHandler,
				HContentPublishedEventHandler, HContentRevokedEventHandler, HContentAccessEventHandler 
{
	//IShareable
	const CLASS_NAME = 'SContentIndex';
	/**
	 * @var SContentIndex
	 */
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @return SContentIndex
	 */
	public static function alloc()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
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
	
	public static function isPublic($managerClass,  $contentId, $checkPubDate = true)
	{
		//self::alloc()->init();
		$DB = DSQL::alloc()->init();
		try
		{
			$managerClass = $DB->escape($managerClass);
			$contentId = $DB->escape($contentId);
			$ignorePubDate = $checkPubDate ? 0 : 1;
			$now = time();
			
			$sql = <<<SQL
SELECT 
		ContentIndex.pubDate 
	FROM ContentIndex 
	LEFT JOIN Managers 
		ON (ContentIndex.managerREL = Managers.managerID) 
	WHERE 
		Managers.manager LIKE '$managerClass' 
		AND ContentIndex.managerContentID LIKE '$contentId'
		AND (
			$ignorePubDate
			OR (
				ContentIndex.pubDate > 0 
				AND ContentIndex.pubDate <= $now
			)
		)
	LIMIT 1
SQL;
			$res = $DB->query($sql);
			list($pd) = $res->fetch();
			$rows = $res->getRowCount();
			$res->free();
			return ($checkPubDate) ? ($pd != 0) : ($rows > 0);
		}
		catch(Exception $e)
		{
			return false;
		}
	}

	public static function getContentInformationBulk(array $cmsids)
	{
		$DB = DSQL::alloc()->init();

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
				$bulk[] = " (ContentIndex.managerContentId LIKE '".$DB->escape($split[1]).
							"' AND Managers.manager LIKE '".$DB->escape($split[0])."') ";
			}
		}
		if(count($bulk))
		{
			try
			{
				$condition = implode('OR', $bulk);
				$sql = <<<SQL
SELECT DISTINCT 
		ContentIndex.managerContentID AS CID, 
		Managers.manager AS Manager, 
		ContentIndex.title as Title, 
		Aliases.alias AS Alias, 
		ContentIndex.pubDate AS PubDate 
	FROM Aliases 
	LEFT JOIN ContentIndex 
		ON (Aliases.contentREL = ContentIndex.contentID) 
	LEFT JOIN Managers 
		ON (ContentIndex.managerREL = Managers.managerID) 
	WHERE 
		AND ($condition)
    ORDER BY Aliases.active DESC
    LIMIT 1
SQL;
				$res = $DB->query($sql, DSQL::ASSOC);
				while ($erg = $res->fetch())
				{
					$result[$erg['Manager'].':'.$erg['CID']] = array(
						'Title' => $erg['Title'], 
						'Alias' => $erg['Alias'], 
						'PubDate' => $erg['PubDate']
					);
				}
				$res->free();
			}
			catch(Exception $e)
			{
			}
		}
		
		return $result;
	}
	
	public static function getTitleAndAlias($manager, $contentId)
	{
		$DB = DSQL::alloc()->init();
		try
		{
			$e_manager = $DB->escape($manager);
			$e_contentId = $DB->escape($contentId);
			$sql = <<<SQL
SELECT 
		ContentIndex.title, 
		Aliases.alias, 
		ContentIndex.pubDate 
	FROM Aliases 
	LEFT JOIN ContentIndex 
		ON (Aliases.contentREL = ContentIndex.contentID) 
	LEFT JOIN Managers 
		ON (ContentIndex.managerREL = Managers.managerID) 
	WHERE 
		ContentIndex.managerContentId LIKE '$e_contentId' 
		AND Managers.manager LIKE '$e_manager'
	ORDER BY Aliases.active DESC
    LIMIT 1
SQL;
			$res = $DB->query($sql, DSQL::NUM);
			if($res->getRowCount() > 0)
			{
				list($Title, $Alias, $PubDate) = $res->fetch();
				$res->free();
			}
			else
			{
				$res->free();
				$sci = SComponentIndex::alloc()->init();
				$cnt = false;
				if($sci->IsExtension($manager, 'BContentManager'))
				{
					$man = new $manager();
					$man = $man->alloc()->init();
					$cnt = $man->Open($contentId);
				}
				if($cnt && $cnt instanceof BContent)
				{
					$Title = $cnt->Title; //FIXME
					$Alias = $manager.':'.$contentId;
					$PubDate = $cnt->PubDate;//FIXME
				}
				else
				{
					throw new Exception('unknown content');
				}
			}
		}
		catch(Exception $e)
		{
			$Title = 'Error 404';
			$Alias = 'MError:404';
			$PubDate = '1';
		}
		return array(
			'Title' 	=> $Title, 
			'Alias' 	=> $Alias,
			'PubDate' 	=> $PubDate
		);
	}	
	
	private function buildSQLForCount($ofManger, $tagged)
	{
		$DB = DSQL::alloc()->init();
		//Define what we want
		$ManagerSQL = ($ofManger == null) ? '1' : 'Managers.manager = \''.$DB->escape($ofManger)."'";
		$tags = STag::parseTagStr($tagged);
		if(!is_array($tags))
		{
			$tags = array();
		}
		
		$sql = <<<SQL
SELECT 
		COUNT(ContentIndex.title) AS Count 
	FROM ContentIndex 
	LEFT JOIN Managers 
		ON (ContentIndex.managerREL = Managers.managerID) 
	WHERE 
		$ManagerSQL
		
SQL;
		foreach ($tags as $tag) 
		{
			$tag = $DB->escape($tag);
			$sql .= <<<SQL
		AND ContentIndex.contentID 
			IN (
				SELECT 
						relContentTags.contentREL 
					FROM relContentTags 
					LEFT JOIN Tags 
						ON (Tags.tagID = relContentTags.tagREL) 
					WHERE 
						Tags.tag = '$tag'
			)
SQL;
		}	
		return $sql;
	}
	
	private function buildSQLForList($items, $offset, $ofManger, $tagged, $latestFirst)
	{
		$DB = DSQL::alloc()->init();
		$ManagerSQL = ($ofManger == null) ? '1' : 'Managers.manager = \''.$DB->escape($ofManger)."'";
		$now = time();
		$order = ($latestFirst) ? "DESC " : "ASC ";
		$limit = '';
		if($items > 0 && is_numeric($items))
		{
			$limit = 'LIMIT '.(($offset > 0 && is_numeric($offset)) 
				? $offset.",".$items
				: $items);
		}
		$tags = STag::parseTagStr($tagged);
		if(!is_array($tags))
		{
			$tags = array();
		}
		$sql = <<<SQL
SELECT 
		ContentIndex.title AS Title, 
		ContentIndex.pubDate AS PubDate, 
		Managers.manager AS Manager,
		ContentIndex.managerContentID AS ContentID 
	FROM ContentIndex 
	LEFT JOIN Managers 
		ON (ContentIndex.managerREL = Managers.managerID) 
	WHERE 
		$ManagerSQL
		AND ContentIndex.pubDate > 0 
		AND ContentIndex.pubDate < $now
SQL;
		foreach ($tags as $tag) 
		{
			$tag = $DB->escape($tag);
			$sql .= <<<SQL
		AND ContentIndex.contentID 
			IN (
				SELECT 
						relContentTags.contentREL 
					FROM relContentTags 
					LEFT JOIN Tags 
						ON (Tags.tagID = relContentTags.tagREL) 
					WHERE 
						Tags.tag = '$tag'
			)
		ORDER BY ContentIndex.pubDate $order
		$limit
SQL;
		}	
		return $sql;
	}
	
	public function getLatest($items = 0, $offset = 0, $ofManger = null, $tagged = "", $latestFirst = true, $fetchAssoc = false)
	{
		$sql = $this->buildSQLForList($items, $offset, $ofManger, $tagged, $latestFirst);
		$return = array();
		$DB = DSQL::alloc()->init();
		try
		{
			$res = $DB->query($sql, ($fetchAssoc ? DSQL::ASSOC : DSQL::NUM));
			while($res->hasNext())
			{
				$return[] = $res->fetch($fetchAssoc);
			}
			$res->free();
		}
		catch(Exception $e)
		{
			return array();
		}
		return $return;
	}
	
	public function countLatest($ofManger = null, $tagged = "")
	{
		$sql = $this->buildSQLForCount($ofManger, $tagged);
		$DB = DSQL::alloc()->init();
		try
		{
			$res = $DB->query($sql, DSQL::NUM);
			list($count) = $res->fetch(SQLITE_NUM);
			$res->free();
		}
		catch(Exception $e)
		{
			$count = 0;
		}
		return $count;
	}
	
	/**
	 * Insert manager into manager db if it does not exist and always return the db id
	 *
	 * @param string $manager
	 * @return int
	 */
	private function getManagerId($manager)
	{
		$DB = DSQL::alloc()->init();
		$manager = $DB->escape($manager);
		$sql = <<<SQL
SELECT managerID 
	FROM Managers 
	WHERE manager LIKE '$manager'
	LIMIT 1
SQL;
		$res = $DB->query($sql, DSQL::NUM);
		list($erg) = $res->fetch();
		$res->free();
		if(!$erg)
		{
			throw new XDatabaseException('Manager not found');
		}
		return $erg;
	}
	
	/**
	 * Update content index
	 *
	 * @param BContent $content
	 */
	private function updateIndex(BContent $content)
	{
		$DB = DSQL::alloc()->init();
		try
		{
			$manager = $content->getManagerName();
			$managerID = $this->getManagerId($manager);
			$contentID = $content->Id;
			$e_managerID = $DB->escape($managerID);
			$e_contentID = $DB->escape($contentID);
			$e_title = $DB->escape($content->Title);
			$e_pubdate = $DB->escape($content->PubDate);
			$e_summary = $DB->escape(mb_substr(preg_replace("/[\\s]+/u"," ",$content->Text),0,1024, 'UTF-8'));
			
			$sql = <<<SQL
SELECT COUNT(managerContentID) 
	FROM ContentIndex 
	WHERE managerContentID = '$e_contentID' 
	AND managerREL = $e_managerID
SQL;
			$res = $DB->query($sql, DSQL::ASSOC);
			$dat = $res->fetch();
			$res->free();
			if(is_array($dat) && $dat[0] == 0)
			{
				$DB->insert(
					'ContentIndex',
					array('managerREL', 'managerContentID', 'title', 'pubDate', 'summary'),
					array(intval($managerID), $contentID, $content->Title, intval($content->PubDate), 
							mb_substr(preg_replace("/[\\s]+/u"," ",$content->Text),0,1024, 'UTF-8')));
			}
			else
			{
				$sql = <<<SQL
UPDATE ContentIndex 
	SET 
		title='$e_title', 
		pubDate=$e_pubdate, 
		summary='$e_summary' 
	WHERE 
		managerContentID LIKE '$e_contentID' 
		AND managerREL = $e_managerID
SQL;
				$res = $DB->queryExecute($sql);
			}
			$DB->insertUnescaped(
					'Changes',
					array('contentREL', 'title', 'size', 'changeDate', 'username'),
					array(
						"(SELECT contentID FROM ContentIndex WHERE managerContentID = '$e_contentID' AND managerREL = $e_managerID)",
						"'".$e_title."'",
						$DB->escape(isset($content->Size) ? intval($content->Size) : 0),
						$DB->escape(time()),
						"'".$DB->escape(PAuthentication::getUserID())."'"
					)
				);
		}
		catch(Exception $e)
		{
			echo "<!-- EX: ".$e->getCode()." ".$e->getMessage()." -- ".$e->getFile()."@".$e->getLine()." ".$e->getTraceAsString()." --> ";
		}
	}
	
	public function getMeta(BContent $content)
	{
		$DB = DSQL::alloc()->init();
		$manager = $content->getManagerName();
		$cid = $content->Id;
		$e_manager = $DB->escape($manager);
		$e_cid = $DB->escape($cid);
		$meta = array();
		
		try
		{
			$sql = <<<SQL
SELECT 
		ContentIndex.title, 
		ContentIndex.pubDate, 
		ContentIndex.contentID 
	FROM ContentIndex 
	LEFT JOIN Managers 
		ON (ContentIndex.managerREL = Managers.managerID) 
	WHERE 
		managerContentID LIKE '$e_cid'
		AND Managers.manager LIKE '$e_manager' 
	LIMIT 1
SQL;
			$res = $DB->query($sql, DSQL::NUM);
			if($res->getRowCount() != 1)
			{
				$res->free();
				throw new Exception('no data');
			}
			list($meta['Title'], $meta['PubDate'], $dbid) = $res->fetch();
			$dbid = $DB->escape(intval($dbid));
			$res->free();
			$sql = <<<SQL

SELECT 
    (SELECT size FROM Changes WHERE contentREL=$dbid ORDER BY changeDate DESC LIMIT 1) AS size,
    (SELECT changeDate FROM Changes WHERE contentREL=$dbid ORDER BY changeDate DESC LIMIT 1) AS Modified,
    (SELECT username FROM Changes WHERE contentREL=$dbid ORDER BY changeDate DESC LIMIT 1) AS Modifier,
    (SELECT changeDate FROM Changes WHERE contentREL=$dbid ORDER BY changeDate ASC LIMIT 1) AS Created,
    (SELECT username FROM Changes WHERE contentREL=$dbid ORDER BY changeDate ASC LIMIT 1) AS Creator
SQL;
//
//SELECT 
//		size, 
//		changeDate, 
//		username 
//	FROM Changes 
//	WHERE 
//		(
//			changeDate = (
//				SELECT changeDate 
//					FROM Changes 
//					WHERE contentREL=$dbid
//					ORDER BY changeDate ASC 
//					LIMIT 1
//			) 
//			OR changeDate = (
//				SELECT changeDate 
//					FROM Changes 
//					WHERE contentREL=$dbid
//					ORDER BY changeDate DESC 
//					LIMIT 1
//			)
//		) 
//		AND contentREL=$dbid
//SQL;
			$res = $DB->query($sql, DSQL::NUM);
//			if($res->getRowCount() != 2)
//			{
//				$res->free();
//				throw new Exception('no data');
//			}
//			list($meta['Size'], $meta['CreateDate'], $meta['CreatedBy']) = $res->fetch();
//			list($meta['Size'], $meta['ModifyDate'], $meta['ModifiedBy']) = $res->fetch();
            list($meta['Size'], $meta['ModifyDate'], $meta['ModifiedBy'], $meta['CreateDate'], $meta['CreatedBy']) = $res->fetch();
			$res->free();
		}
		catch (Exception $e)
		{
			
		}
		return $meta;
	}
	
	public function getIndex(BContentManager $manager)
	{
		$DB = DSQL::alloc()->init();
		$e_manager = $DB->escape(get_class($manager));
		$sql = <<<SQL
SELECT 
		ContentIndex.managerContentId, 
		ContentIndex.Title 
	FROM ContentIndex 
	LEFT JOIN Managers 
		ON(ContentIndex.managerREL = Managers.managerID) 
	WHERE 
		ContentIndex.pubDate > -1 
		AND Managers.manager LIKE '$e_manager' 
	ORDER BY ContentIndex.Title ASC
SQL;
		try
		{
			$res = $DB->query($sql, DSQL::NUM);
			$index = array();
			while ($arr = $res->fetch())
			{
				$index[$arr[0]] = $arr[1];
			}
			$res->free();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$index = array();
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
		$DB = DSQL::alloc()->init();
		$manager = $content->getManagerName();
		$managerID = $this->getManagerId($manager);
		$contentID = $content->Id;
		
		$e_manager = $DB->escape(intval($managerID));
		$e_cid = $DB->escape($contentID);

		$DB->beginTransaction();
		try
		{
			$sql = <<<SQL
UPDATE ContentIndex 
	SET 
		title='', 
		pubDate=-1, 
		summary='' 
	WHERE 
		managerContentID LIKE '$e_cid' 
		AND managerREL = $e_manager;
SQL;
			$DB->queryExecute($sql);
			$DB->insertUnescaped(
				'Changes',
				array('contentREL', 'title', 'size', 'changeDate', 'username'),
				array(
					"(SELECT contentID FROM ContentIndex WHERE managerContentID = '$e_cid' AND managerREL = $e_manager)",
					'',
					-1,
					$DB->escape(time()),
					$DB->escape(PAuthentication::getUserID()."@".$_SERVER['REMOTE_ADDR'])
				)
			);
		}
		catch(Exception $e)
		{
			$DB->rollback();
		}
		$DB->commit();

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