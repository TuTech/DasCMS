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
	
	private function setTags($alias, $tagstring)
	{
		$tags = self::parseTagStr($tagstring);
		$DB = DSQL::getSharedInstance();
		try
		{
		    $DB->beginTransaction();
			$res = QSTag::getContentDBID($alias);
			if($res->getRowCount() != 1)
			{
				$res->free();
				throw new Exception('wrong number of content ids');
			}
			list($CID) = $res->fetch();
			$res->free();
			//remove links
			QSTag::removeRelationsTo($CID);
			$tagval = array();
			foreach ($tags as $tag) 
			{
				$tagval[] = array($tag);
			}
			if(count($tagval) > 0)
			{
				QSTag::dumpNewTags($tagval);
			}		
			QSTag::linkTagsTo($tags, $CID);
			$DB->commit();
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
			echo $e->getTraceAsString();
			$DB->rollback();
			return false;
		}
		return true;
	}
	
	private function getTags($alias)
	{
		$tags = array();
		$res = QSTag::listTagsOf($alias);
		while($tag = $res->fetch())
		{
			$tags[] = $tag[0];
		}
		$res->free();
		return $tags;
	}
	
	/**
	 * Assign tags to a content-element
	 *
	 * @param BContent $content
	 */
	public function update(BContent $content)
	{
		$this->setTags($content->Alias, implode(',', $content->Tags));
	}
	
	/**
	 * Assign tags to a content-element
	 *
	 * @param BContent $content
	 * @param string $tagstr
	 */
	public function set(BContent $content, $tagstr)
	{
		$this->setTags($content->Alias, $tagstr);
	}
	
	/**
	 * Get all tags assigned to a content-element
	 *
	 * @param BContent $content
	 * @return array
	 */
	public function get($BContentOrAlias)
	{
	    $alias = ($BContentOrAlias instanceof BContent) 
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