<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class SAlias extends BSystem implements IShareable,IUseSQLite,
			HContentChangedEventHandler, HContentCreatedEventHandler, HContentDeletedEventHandler,
			HContentPublishedEventHandler 

{	
	/**
	 * @param EContentChangedEvent $e
	 */
	public function HandleContentChangedEvent(EContentChangedEvent $e)
	{
		$this->updateAlias($e->Content);
	}
	
	/**
	 * @param EContentCreatedEvent $e
	 */
	public function HandleContentCreatedEvent(EContentCreatedEvent $e)
	{
		$this->updateAlias($e->Content);
	}
	
	/**
	 * @param EContentDeletedEvent $e
	 */
	public function HandleContentDeletedEvent(EContentDeletedEvent $e)
	{
		$this->removeAliases($e->Content);
	}

	/**
	 * @param EContentDeletedEvent $e
	 */
	public function HandleContentPublishedEvent(EContentPublishedEvent $e)
	{
		$this->updateAlias($e->Content);
	}
	
	


/**
	 * Generate unified alias from string
	 * to be called by updateAlias()
	 *
	 * @param BContent $content
	 * @return string
	 */
	private function prepareForURL(BContent $content)
	{
		return $this->getUnifiedAlias($content->Title, $content->PubDate);
	}
	
	private function getUnifiedAlias($title, $pubdate)
	{
		$cfg = Configuration::alloc();
		$cfg->init();
		$prefix = '';
		if(true)// $cfg->get('AliasPubDatePrefix'))
		{
			$prefix .= date('Y-m-d-',$pubdate);
		}
		//@todo consult ascii conversion tools IConvertToASCII
		$dechars = array('ä' => 'ae', 'Ä' => 'Ae', 'ö' => 'oe', 
						'Ö' => 'Oe','ü' => 'ue', 'Ü' => 'Ue', 'ß' => 'ss');
		foreach ($dechars as $chr => $rep) 
		{
			$title = str_replace($chr, $rep, $title);
		}
		
		$title = preg_replace('/[^a-zA-Z0-9\._]+/i', '-',$title);
		return substr($prefix.$title,0,64);
	}
	
	/**
	 * Handle alias assignments
	 * to be called by event handlers for content surveillance
	 *
	 * @param BContent $content
	 */
	public function updateAlias(BContent $content)
	{
		if($content->PubDate == 0)
		{
			return;
		}
		$connection = DSQLite::alloc()->init();
		$manager = $content->getManagerName();
		$nice = $this->prepareForURL($content);
		$sql = "SELECT Aliases.alias AS alias, ContentIndex.managerContentID AS cid, ".
					"Managers.manager AS manager FROM Aliases ".
					"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
					"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
					"WHERE alias LIKE '".sqlite_escape_string($nice)."'";
		$res = $connection->query($sql, SQLITE_ASSOC);
		$ergc = 0;
		$preverg = array();
		while($erg = $res->fetch())
		{
			$preverg = $erg;
			$ergc++;
		}
		$lookup = null;
		$newAlias = null;
		if($ergc == 0 || ($preverg['manager'] == $manager && $preverg['cid'] == $content->Id))
		{
			$newAlias = $nice;
			//update activate enforce
		}
		else
		{
			$numerified = substr($nice,0,58).'~'; //5-digit numbers possible
			//find $numerified aliases for current content
			$sql = "SELECT Aliases.alias AS alias, ContentIndex.managerContentID AS cid, ".
						"Managers.manager AS manager FROM Aliases ".
						"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
						"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
						"WHERE alias LIKE '".sqlite_escape_string($numerified)."%' ".
						"AND manager LIKE '".sqlite_escape_string($manager)."' ".
						"AND cid LIKE '".sqlite_escape_string($content->Id)."' ".
						"ORDER BY alias ASC";
			$res = $connection->query($sql, SQLITE_ASSOC);
			if($res->numRows() > 0)
			{
				//use existing numerified alias
				$erg = $res->fetch();
				$newAlias = $erg['alias'];
			}
			else
			{
				//create new numerified alias
				$sql = "SELECT alias FROM Aliases ".
							"WHERE alias LIKE '".sqlite_escape_string($numerified)."%' ORDER BY alias ASC";
				$res = $connection->query($sql, SQLITE_ASSOC);
				$usedNumbers = array();
				$offset = strlen($numerified);
				//get all numbers
				while($erg = $res->fetch())
				{
					$usedNumbers[substr($erg['alias'], $offset)] = '';
				}
				//find first unused
				for ($i = 1; $i <= 4096; $i++)
				{
					 if(!array_key_exists($i, $usedNumbers))
					 {
					 	$newAlias = $numerified.$i;
					 	break;
					 }
				}
			}
		}
		if($newAlias != null)
		{
			$this->setActive($newAlias, $content);
		}
	}
	
	public function removeAliases(BContent $content)
	{
		$manager = $content->getManagerName();
		$sql = "DELETE FROM Aliases WHERE contentREL = ".
					"(".
						"SELECT ContentIndex.contentID FROM ContentIndex LEFT JOIN Managers ".
							"ON (ContentIndex.managerREL = Managers.managerID) ".
							"WHERE Managers.manager LIKE '".sqlite_escape_string($manager)."' ".
							"AND ContentIndex.managerContentID LIKE '".sqlite_escape_string($content->Id)."'".
					")";
		return DSQLite::alloc()->init()->queryExec($sql);
	}
	
	public function rebuildAliases()
	{
		$connection = DSQLite::alloc()->init();
		$connection->queryExec("BEGIN TRANSACTION");
		$connection->queryExec("DELETE FROM Aliases WHERE 1");
		$res = $connection->query("SELECT contentID,title, pubDate FROM ContentIndex WHERE pubDate > 0", SQLITE_NUM);
		$newAliases = array();
		$success = true;
		while($erg = $res->fetch())
		{
			list($id, $title, $pubdate) = $erg;
			$sql = "INSERT INTO Aliases (alias, active, contentREL) VALUES ('".
					sqlite_escape_string($this->getUnifiedAlias($title, $pubdate))."','1','".
					sqlite_escape_string($id).
					"')";
			echo '<!-- ', $sql, " --> \n\n";
			$success = $connection->queryExec($sql, $err);
			var_dump($success);
			if(!$success)
			{
				$connection->queryExec("ROLLBACK");
				SNotificationCenter::alloc()->init()->report('warning', $err);
				break;
			}
		}
		if($success)
		{
			$connection->queryExec("COMMIT");
		}
	}
	
	/**
	 * Activate alias or create active alias for $content
	 *
	 * @param string $alias
	 * @param BContent $content
	 * @return boolean
	 */
	private function setActive($alias, BContent $content)
	{
		$connection = DSQLite::alloc()->init();
		$manager = $content->getManagerName();
		$cid = $content->Id;
		$sql = array();
		$sql[0] = 
			"BEGIN TRANSACTION";
		$sql[1] = 
			"INSERT OR IGNORE INTO Aliases (alias, contentREL) ".
				"VALUES (".
					"'".sqlite_escape_string($alias)."', ".
					"(".
						"SELECT ContentIndex.contentID FROM ContentIndex LEFT JOIN Managers ".
							"ON (ContentIndex.managerREL = Managers.managerID) ".
							"WHERE Managers.manager LIKE '".sqlite_escape_string($manager)."' ".
							"AND ContentIndex.managerContentID LIKE '".sqlite_escape_string($cid)."' LIMIT 1".
					")".
				")";
		$sql[2] =
			"UPDATE Aliases SET active = 0 WHERE aliasID IN (".
					"SELECT Aliases.aliasID FROM Aliases ".
						"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
						"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
						"WHERE Managers.manager LIKE '".sqlite_escape_string($manager)."' ".
						"AND ContentIndex.managerContentID LIKE '".sqlite_escape_string($cid)."' ".
				")";
		$sql[3] =
			"UPDATE Aliases SET active = 1 WHERE alias LIKE '".sqlite_escape_string($alias)."'"
		;
		$commit = "COMMIT";
		$rollback = 'ROLLBACK';
		$success = true;
		$err = null;
		foreach ($sql as $cmd) 
		{
			$exec = $connection->queryExec($cmd, $err);
			if(!$exec)
			{
				$success = false;
				break;
			}
		}
		if($success)
		{
			$connection->queryExec($commit);
		}
		else
		{
			echo '<!-- SQLite error ',$err,'',$cmd,' -->';
			SNotificationCenter::alloc()->init()->report('warning', $err);
			$connection->queryExec($rollback);
		}
		return $success;
	}
	
	
	/*****************************brainstorm temp stuff*************************************/
	
	
	/**
	 * Get content object by alias
	 *
	 * @param string $alias
	 * @return BContent|null
	 */
	public static function resolve($alias, $toObject = true)
	{
		$connection = DSQLite::alloc()->init();
		$sql = "SELECT Managers.manager,ContentIndex.managerContentID FROM Aliases ".
					"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
					"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
					"WHERE Aliases.alias LIKE '".sqlite_escape_string($alias)."'";
		$res = $connection->query($sql, SQLITE_NUM);
		if($res->numRows() > 0)
		{
			 list($manager, $id) = $res->fetch();
		}
		else
		{
			if(strpos($alias, ':') == false)
				return null;
			list($manager, $id) = explode(':', $alias);
		}
		
		$sci = SComponentIndex::alloc()->init();
		if($sci->IsExtension($manager, 'BContentManager'))
		{
			if($toObject)
			{
				$man = new $manager();
				$man = $man->alloc()->init();
				return $man->Open($id);
			}
			else
			{
				return $manager.':'.$id;
			}
		}
		return null;
	}
	
	public static function match($alias_a,$alias_b)
	{
		// null != null
		$cid_a = self::resolve($alias_a, false);
		if($cid_a == null)
		{
			return false;
		}
		$cid_b = self::resolve($alias_b, false);
		if($cid_b == null)
		{
			return false;
		}
		return $cid_a == $cid_b;
	}
	
	
	/**
	 * Get all assigned aliases in an array
	 *
	 * @param BContent $content
	 * @return array
	 */
	public function getAllAssigned(BContent $content)
	{
		
	}
	
	/**
	 * Get active alias 
	 *
	 * @param BContent $content
	 * @return string
	 */
	public static function getCurrent(BContent $content)
	{
		$dat = SContentIndex::getTitleAndAlias($content->getManagerName(), $content->Id);
		return $dat['Alias'];
	}

	
	/**
	 * Get active aliases for all content objects by id (Manager:c-id)
	 *
	 * @param array $contentObjects
	 * @return array
	 */
	public function getAllActive(array $contents)
	{
		
	}
	
	/**
	 * Check existance of an alias
	 *
	 * @param string $alias
	 * @return bool
	 */
	public function exists($alias)
	{
		
	}

	//begin IShareable
	const Class_Name = 'SAlias';
	
	public static $sharedInstance = NULL;
	
	/**
	 * Enter description here...
	 *
	 * @return SAlias
	 */
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    /**
     * initialize whatever
     *
     * @return SAlias
     */
    function init()
    {
    	return $this;
    }
	//end IShareable
}
?>