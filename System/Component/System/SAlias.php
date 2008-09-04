<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class SAlias extends BSystem implements IShareable,
			HContentChangedEventHandler, HContentCreatedEventHandler, HContentDeletedEventHandler,
			HContentPublishedEventHandler 
{	
	
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
		$manager = $content->getManagerName();
		$nice = $this->prepareForURL($content);
		
		$DB = DSQL::alloc()->init();
		try
		{
			$res = $DB->query("SELECT Aliases.alias AS alias, ContentIndex.managerContentID AS cid, ".
					"Managers.manager AS manager FROM Aliases ".
					"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
					"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
					"WHERE alias LIKE '".$DB->escape($nice)."'", DSQL::ASSOC);
			$ergc = $res->getRowCount();
			if($ergc > 0)
			{
				$res->seekRow($ergc-1);
				$preverg = $res->fetch();
			} 
			$res->free();
		}
		catch(Exception $e)
		{
			$ergc = 0;
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
			try
			{
				$res = $DB->query("SELECT Aliases.alias AS alias, ContentIndex.managerContentID AS cid, ".
						"Managers.manager AS manager FROM Aliases ".
						"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
						"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
						"WHERE alias LIKE '".$DB->escape($numerified)."%' ".
						"AND manager LIKE '".$DB->escape($manager)."' ".
						"AND cid LIKE '".$DB->escape($content->Id)."' ", DSQL::ASSOC);
				$rows = $res->getRowCount();
				$res->free();
			}
			catch(Exception $e)
			{
				$rows = 0;
			}
			
			if($rows > 0)
			{
				//use existing numerified alias
				$erg = $res->fetch();
				$newAlias = $erg['alias'];
			}
			else
			{
				try
				{
					$res = $DB->query("SELECT alias FROM Aliases ".
							"WHERE alias LIKE '".$DB->escape($numerified)."%' ORDER BY alias ASC", DSQL::ASSOC);
								//create new numerified alias
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
					$res->free();	
				}
				catch(Exception $e)
				{
					$newAlias = null;
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
		$DB = DSQL::alloc()->init();
		$DB->beginTransaction();
		try
		{
			$DB->queryExecute(
				"DELETE FROM Aliases WHERE contentREL = ".
				"(".
					"SELECT ContentIndex.contentID FROM ContentIndex LEFT JOIN Managers ".
						"ON (ContentIndex.managerREL = Managers.managerID) ".
						"WHERE Managers.manager LIKE '".$DB->escape($manager)."' ".
						"AND ContentIndex.managerContentID LIKE '".$DB->escape($content->Id)."'".
				")");
		}
		catch (Exception $e)
		{
			$DB->rollback();
			return false;
		}
		$DB->commit();
		return true;
	}
	
	public function rebuildAliases()
	{
		$DB = DSQL::alloc()->init();
		$DB->beginTransaction();
		try
		{
			$DB->queryExecute("DELETE FROM Aliases WHERE 1");
			$res = $DB->query("SELECT contentID,title, pubDate FROM ContentIndex WHERE pubDate > 0", DSQL::NUM);
			$values = array();
			while($res->hasNext())
			{
				list($id, $title, $pubdate) = $res->fetch();
				$values[] = array(
					$this->getUnifiedAlias($title, $pubdate),
					1,
					$id
				);
			}
			$res->free();
			$DB->insert('Aliases', array('alias', 'active', 'contentREL'), $values);
		}
		catch (Exception $e)
		{
			$DB->rollback();
			return;
		}
		$DB->commit();
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
		$DB = DSQL::alloc()->init();
		$manager = $content->getManagerName();
		$cid = $content->Id;
		$DB->beginTransaction();
		try
		{
			$DB->insertUnescaped(
				'Aliases',
				array('alias','contentREL'),
				array(
					"'".$DB->escape($alias)."'",
					"(".
						"SELECT ContentIndex.contentID FROM ContentIndex LEFT JOIN Managers ".
							"ON (ContentIndex.managerREL = Managers.managerID) ".
							"WHERE Managers.manager LIKE '".$DB->escape($manager)."' ".
							"AND ContentIndex.managerContentID LIKE '".$DB->escape($cid)."' LIMIT 1".
					")"
				)
			);
	
			$DB->queryExecute(
				"UPDATE Aliases SET active = 0 WHERE aliasID IN (".
					"SELECT Aliases.aliasID FROM Aliases ".
						"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
						"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
						"WHERE Managers.manager LIKE '".$DB->escape($manager)."' ".
						"AND ContentIndex.managerContentID LIKE '".$DB->escape($cid)."' ".
				")"
			);
				
			$DB->queryExecute(
				"UPDATE Aliases SET active = 1 WHERE alias LIKE '".$DB->escape($alias)."'"
			);
		}
		catch(Exception $e)
		{
			$DB->rollback();
			return false;
		}
		$DB->commit();
		return true;
	}
	
	/**
	 * Get content object by alias
	 *
	 * @param string $alias
	 * @return BContent|null
	 */
	public static function resolve($alias, $toObject = true)
	{
		$DB = DSQL::alloc()->init();
		$failed = true;
		try
		{
			$res = $DB->query("SELECT Managers.manager,ContentIndex.managerContentID FROM Aliases ".
					"LEFT JOIN ContentIndex ON (Aliases.contentREL = ContentIndex.contentID) ".
					"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
					"WHERE Aliases.alias LIKE '".$DB->escape($alias)."'", DSQL::NUM);	
			if($res->getRowCount() > 0) 
			{
				list($manager, $id) = $res->fetch();
				$failed = false;
			}
			$res->free();
		}
		catch(Exception $e){/*handled in following if*/}
		if($failed)
		{
			if(strpos($alias, ':') == false)
			{
				return null;
			}
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