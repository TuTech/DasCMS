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
    extends 
        BObject 
    implements 
        IShareable,
		HContentChangedEventHandler, 
		HContentCreatedEventHandler, 
		HContentDeletedEventHandler,
		HContentPublishedEventHandler 
{	
	public static function cleanAlias($untrustedAlias){
		return $untrustedAlias;// preg_replace('/[^a-zA-Z0-9\._-]+/mui', '', $untrustedAlias);
	}

	/**
	 * Handle alias assignments
	 * to be called by event handlers for content surveillance
	 *
	 * @param Interface_Content $content
	 */
	public function updateAlias(Interface_Content $content)
	{
		if($content->PubDate == 0)
		{
			return;
		}
		$nice = $this->getUnifiedAlias($content->Title, $content->PubDate);
		
		try
		{
		    $insertAlias = $nice;
		    $dbid = $this->resolveAliasToId($content->Alias);
		    for($i = 1; $i <= 9999; $i++)
		    {
				$isOk = Core::Database()
					->createQueryForClass('SAlias')
					->call('isAliasAssigned')
					->withParameters($newAlias, $dbid)
					->fetchSingleValue();
				if(!$isOk){
					$isOk = Core::Database()
						->createQueryForClass('SAlias')
						->call('addAlias')
						->withParameters($newAlias, $dbid)
						->execute();
				}
		        if($isOk == 1)
		        {
		            break;
		        }
		        $insertAlias = substr($nice,0,58).'~'.$i;
		    }
		    Core::Database()
				->createQueryForClass('SAlias')
				->call('setActive')
				->withParameters($alias, $alias)
				->execute();
		}
		catch(Exception $e)
		{
		}
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
	 * @param string $title
	 * @param int $pubDate
	 * @return string
	 */
	private function getUnifiedAlias($title, $pubdate)
	{
		$suffix = '';
		if(true)// $cfg->get('AliasPubDatePrefix'))
		{
			$suffix .= date('-Y-m-d',$pubdate);
		}
		//@todo consult ascii conversion tools IConvertToASCII
		$dechars = array('ä' => 'ae', 'Ä' => 'Ae', 'ö' => 'oe', 
						'Ö' => 'Oe','ü' => 'ue', 'Ü' => 'Ue', 'ß' => 'ss');
		foreach ($dechars as $chr => $rep) 
		{
			$title = str_replace($chr, $rep, $title);
		}
		
		$title = preg_replace('/[^a-zA-Z0-9\._]+/i', '-',$title);
		return substr($title,0,64-strlen($suffix)).$suffix;
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

		//build parameter info
		$placeHolder = array();
		$toMatch = count($aliasesToMatch);
		for($i = 0; $i < $toMatch; $i++){
			if(empty($aliasesToMatch[$i])){
				unset($aliasesToMatch[$i]);
			}
			else{
				$placeHolder[] = '?';
			}
		}

		$toMatch = count($aliasesToMatch);
		if($toMatch == 0){
			return false;
		}
		$aliasesToMatch[] = $id;

		//define parameters
		$def = array();
		$def[Interface_Database_CallableQuery::PARAMETER_DEFINITION] = str_repeat('s', $toMatch).'i';

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
	        ? $content->Alias 
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

	//begin IShareable
	const CLASS_NAME = 'SAlias';
	
	public static $sharedInstance = NULL;
	
	/**
	 * Enter description here...
	 *
	 * @return SAlias
	 */
	public static function getSharedInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end IShareable
}
?>