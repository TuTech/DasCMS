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
	extends 
	    BSystem 
	implements 	
	    IShareable, 
		HContentChangedEventHandler, 
		HContentCreatedEventHandler, 
		HContentDeletedEventHandler,
		HContentPublishedEventHandler, 
		HContentRevokedEventHandler, 
		HContentAccessEventHandler 
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
	
	public static function exists($alias)
	{
	    $res = QSContentIndex::getDBID($alias);
	    $erg = $res->getRowCount();
	    $res->free();
	    return $erg == 1;
	}

	public static function getContentInformationBulk(array $aliases)
	{
	    $res = QSContentIndex::getPrimaryAliases($aliases);
	    $map = array();
	    $revmap = array();
	    $infos = array();
	    while ($erg = $res->fetch())
		{
		    list($reqest, $primary) = $erg;
		    $map[] = $primary;
		    $revmap[$primary] = $reqest;
		}
	    $res->free();
	    
	    $res = QSContentIndex::getBasicInformation($map);
	    while ($erg = $res->fetch())
		{
		    list($title, $pubdate, $alias) = $erg;
		    $infos[$revmap[$alias]] = array(
		        'Title' => $title, 
				'Alias' => $alias,
				'PubDate' => strtotime($pubdate)
			);
		}
		$res->free();
		return $infos;
	}
	
	public static function getTitleAndAlias($alias)
	{
	    $ar =  $this->getContentInformationBulk(array($alias));
	    if(count($ar) == 1)
	    {
	        return array_pop($ar);
	    }
	    else
	    {
        	return array(
    			'Title' 	=> 'Error 404', 
    			'Alias' 	=> 'MError:404',
    			'PubDate' 	=> 1
			); 
	    }
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
			$managerID = $manager;//FIXME manager db id
			$contentID = $content->Id;
			$e_managerID = $DB->escape($managerID);
			$e_contentID = $DB->escape($contentID);
			$e_title = $DB->escape($content->Title);
			$e_pubdate = $DB->escape($content->PubDate);
			$e_summary = $DB->escape(mb_substr(preg_replace("/[\\s]+/u"," ",$content->Text),0,1024, 'UTF-8'));
			
			$sql = "
SELECT COUNT(managerContentID) 
	FROM ContentIndex 
	WHERE managerContentID = '$e_contentID' 
	AND managerREL = $e_managerID
";
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
				$sql = "
UPDATE ContentIndex 
	SET 
		title='$e_title', 
		pubDate=$e_pubdate, 
		summary='$e_summary' 
	WHERE 
		managerContentID LIKE '$e_contentID' 
		AND managerREL = $e_managerID
";
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
	    QSContentIndex::getMetaInformation($content->Alias);
	    
	    
	    
		$DB = DSQL::alloc()->init();
		$manager = $content->getManagerName();
		$cid = $content->Id;
		$e_manager = $DB->escape($manager);
		$e_cid = $DB->escape($cid);
		$meta = array();
		
		try
		{
			$sql = "
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
";
			$res = $DB->query($sql, DSQL::NUM);
			if($res->getRowCount() != 1)
			{
				$res->free();
				throw new Exception('no data');
			}
			list($meta['Title'], $meta['PubDate'], $dbid) = $res->fetch();
			$dbid = $DB->escape(intval($dbid));
			$res->free();
			$sql = "

SELECT 
    (SELECT size FROM Changes WHERE contentREL=$dbid ORDER BY changeDate DESC LIMIT 1) AS size,
    (SELECT changeDate FROM Changes WHERE contentREL=$dbid ORDER BY changeDate DESC LIMIT 1) AS Modified,
    (SELECT username FROM Changes WHERE contentREL=$dbid ORDER BY changeDate DESC LIMIT 1) AS Modifier,
    (SELECT changeDate FROM Changes WHERE contentREL=$dbid ORDER BY changeDate ASC LIMIT 1) AS Created,
    (SELECT username FROM Changes WHERE contentREL=$dbid ORDER BY changeDate ASC LIMIT 1) AS Creator
";
			$res = $DB->query($sql, DSQL::NUM);
            list($meta['Size'], $meta['ModifyDate'], $meta['ModifiedBy'], $meta['CreateDate'], $meta['CreatedBy']) = $res->fetch();
			$res->free();
		}
		catch (Exception $e)
		{
			
		}
		return $meta;
	}
	
	/**
	 * @return array (managerContentId => Title)
	 */
	public function getIndex($class)
	{
	    if(is_object($class))
	    {
	        $class = get_class($class);
	    }
		try
		{
		    $res = QSContentIndex::getBasicInformationForClass($class);
			$index = array();
			while ($arr = $res->fetch())
			{
			    list($title, $pubdate, $alias) = $arr; 
				$index[$alias] = $title;
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
	    $dbid = QSContentIndex::getDBID($content->Alias);
	    QSContentIndex::deleteContent($dbid);
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