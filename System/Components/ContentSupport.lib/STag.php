<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-03-05
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class STag 
    extends 
        BObject 
    implements 
        IShareable, 
    	HContentChangedEventHandler, 
    	HContentCreatedEventHandler,
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
	const CLASS_NAME = 'STag';
	/**
	 * @var STag
	 */
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @return STag
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
	//end IShareable
	
	
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

	public function addTags(array $tags){
		foreach ($tags as $tag)
		{
			Core::Database()
				->createQueryForClass('STag')
				->call('setTag')
				->withParameters($tag, $tag)
				->execute();
		}
	}


	private function setTags($alias, $tagstring)
	{
		$tags = self::parseTagStr($tagstring);
		$DB = DSQL::getInstance();
		$ptok = SProfiler::profile(__FILE__, __LINE__, 'updating tags to '.implode(', ', $tags));
		try
		{
		    $DB->beginTransaction();
			$CID = Core::Database()
				->createQueryForClass('STag')
				->call('aliasToId')
				->withParameters($alias)
				->fetchSingleValue();

			Core::Database()
				->createQueryForClass('STag')
				->call('unlink')
				->withParameters($CID)
				->execute();
			//remove links
			foreach ($tags as $tag) 
			{
				Core::Database()
					->createQueryForClass('STag')
					->call('setTag')
					->withParameters($tag, $tag)
					->execute();
				Core::Database()
					->createQueryForClass('STag')
					->call('linkTag')
					->withParameters($CID, $tag)
					->execute();
			}
			$DB->commit();
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
			echo $e->getTraceAsString();
			$DB->rollback();
			SProfiler::finish($ptok);
			return false;
		}
		SProfiler::finish($ptok);
		return true;
	}
	
	private function getTags($alias)
	{
		return Core::Database()
			->createQueryForClass('STag')
			->call('listTagsOf')
			->withParameters($alias)
			->fetchList();
	}
	
	/**
	 * Assign tags to a content-element
	 *
	 * @param Interface_Content $content
	 */
	public function update(Interface_Content $content)
	{
		$this->setTags($content->Alias, implode(',', $content->Tags));
	}
	
	/**
	 * Assign tags to a content-element
	 *
	 * @param Interface_Content $content
	 * @param string $tagstr
	 */
	public function set(Interface_Content $content, $tagstr)
	{
		$this->setTags($content->Alias, $tagstr);
	}
	
	/**
	 * Get all tags assigned to a content-element
	 *
	 * @param Interface_Content $content
	 * @return array
	 */
	public function get($BContentOrAlias)
	{
	    $alias = ($BContentOrAlias instanceof Interface_Content)
	        ? $BContentOrAlias->Alias 
	        : $BContentOrAlias;
		return $this->getTags($alias);
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