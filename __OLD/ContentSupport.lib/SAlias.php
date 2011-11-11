<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SAlias 
    implements 
        Interface_Singleton,
		Event_Handler_ContentChanged,
		Event_Handler_ContentCreated,
		Event_Handler_ContentPublished
{
	const ALIAS_MAX_LENGTH = 64;

	/**
	 * Handle alias assignments
	 * to be called by event handlers for content surveillance
	 *
	 * @param Interface_Content $content
	 */
	public function updateAlias(Interface_Content $content)
	{
		$Db =  Core::Database()->createQueryForClass($this);
		//debulication is invoked later -> check for correct pubdate
		if(!$content->isPublished() || !is_int($content->getPubDate()))
		{
			return;
		}
		$nice = $this->getUnifiedAlias($content->getTitle(), $content->getPubDate());
		try
		{
		    $insertAlias = $nice;
		    $dbid = $this->resolveAliasToId($content->getAlias());
		    for($i = 1; $i <= 999; $i++)
		    {
				$isOk = $Db->call('isAliasAssigned')
					->withParameters($insertAlias, $dbid)
					->fetchSingleValue();
				if(!$isOk){
					$isOk = $Db->call('addAlias')
						->withParameters($insertAlias, $dbid)
						->execute();
				}
		        if($isOk == 1)
		        {
		            break;
		        }
		        $insertAlias = substr($nice,0,58).'-'.$i;
		    }
		    $Db->call('setActive')
				->withParameters($insertAlias, $insertAlias)
				->execute();
		}
		catch(Exception $e)
		{
		}
	}	
	
	/**
	 * @param Event_ContentChanged $e
	 */
	public function handleEventContentChanged(Event_ContentChanged $e)
	{
		$this->updateAlias($e->getContent());
	}
	
	/**
	 * @param Event_ContentCreated $e
	 */
	public function handleEventContentCreated(Event_ContentCreated $e)
	{
		$this->updateAlias($e->getContent());
	}

	/**
	 * @param Event_ContentPublished $e
	 */
	public function handleEventContentPublished(Event_ContentPublished $e)
	{
		$this->updateAlias($e->getContent());
	}
	

	/**
	 * Generate unified alias from string
	 * to be called by updateAlias()
	 *
	 * @param string $title
	 * @param int $pubDate
	 * @return string
	 */
	private function getUnifiedAlias($title, $pubdate)
	{
		$title = strtolower(trim($title));
		
		//get format string
		$dateFormat = Core::Settings()->getOrDefault('content.alias.format', '@-Y-m-d');
		if(strpos($dateFormat, '@') === false){
			$dateFormat = '@'.$dateFormat;
		}

		//apply date to format string
		$dated = strtolower(date($dateFormat, $pubdate));
		$dated = preg_replace('/[^a-z0-9@]+/', '-',$dated);

		//expand title chars
		$dechars = array('ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss');
		$title = str_replace(array_keys($dechars), array_values($dechars), $title);
		$title = preg_replace('/[^a-z0-9]+/', '-',$title);

		//build alias
		$titleLength = self::ALIAS_MAX_LENGTH - strlen($dated) + 1 /* 1 for the @ char */;
		$alias = str_replace('@', substr($title, 0, $titleLength), $dated);

		return $alias;
	}
	
	/**
	 * check if 2 aliases point to the same content
	 */
	public static function match($alias_a,$alias_b)
	{
	    if($alias_a == $alias_b)
	    {
	        return true;
	    }
	    $differentContents = Core::Database()
			->createQueryForClass('SAlias')
			->call('match')
			->withParameters($alias_a, $alias_b)
			->fetchSingleValue();

        return $differentContents == 1;
	}
	
	public static function getMatching($id, array $aliasesToMatch)
	{
		//validate input
		$aliasesToMatch = array_unique(array_values($aliasesToMatch));

		//remove empty
		$toMatch = count($aliasesToMatch);
		for($i = 0; $i < $toMatch; $i++){
			if(empty($aliasesToMatch[$i])){
				unset($aliasesToMatch[$i]);
			}
		}

		//reset array index
		$aliasesToMatch = array_values($aliasesToMatch);
		$toMatch = count($aliasesToMatch);

		//nothing to match
		if($toMatch == 0){
			return false;
		}

		//set placeholders
		$placeHolder = array();
		foreach ($aliasesToMatch as $nil){
			$placeHolder[] = '?';
		}

		//add compare id
		$aliasesToMatch[] = $id;

		//define parameters
		$def = array();
		$def[Interface_Database_CallableQuery::PARAMETER_DEFINITION] = str_repeat('s', count($placeHolder)).'i';

		//build sql and query database
		$match = Core::Database()
			->createQueryForClass('SAlias')
			->buildAndCall(
					'getMatching',
					array(implode(' OR alias = ', $placeHolder)),
					$def
				)
			->withParameterArray($aliasesToMatch)
			->fetchSingleValue();
		return empty($match) ? null : $match;
	}

	
	/**
	 * Get all assigned aliases in an array
	 *
	 * @param Interface_Content $content
	 * @return array
	 */
	public function getAllAssigned(Interface_Content $content)
	{
		
	}
	
	/**
	 * Get active alias 
	 *
	 * @param Interface_Content $content
	 * @return string
	 */
	public static function getCurrent($content)
	{
	    $alias = (is_object($content) && $content instanceof Interface_Content)
	        ? $content->getAlias()
	        : $content;
		$alias = Core::Database()
			->createQueryForClass('SAlias')
			->call('getPrimary')
			->withParameters($someAlias)
			->fetchSingleValue();
		return empty ($alias) ? null : $alias;
	}

	/**
	 * Check existance of an alias
	 *
	 * @param string $alias
	 * @return bool
	 */
	public function exists($alias)
	{
		return $this->resolveAliasToId($alias) === null;
	}

	protected function resolveAliasToId($alias)
	{
		$contentID = Core::Database()
			->createQueryForClass('SAlias')
			->call('resolve')
			->withParameters($alias)
			->fetchSingleValue();
		return empty ($contentID) ? null : $contentID;
	}

	//begin Interface_Singleton
	const CLASS_NAME = 'SAlias';
	
	public static $sharedInstance = NULL;
	
	/**
	 * Enter description here...
	 *
	 * @return SAlias
	 */
	public static function getInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end Interface_Singleton
}
?>