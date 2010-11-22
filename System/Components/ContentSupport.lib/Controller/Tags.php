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
class Controller_Tags
    implements 
        Interface_Singleton, 
    	Event_Handler_ContentChanged,
    	Event_Handler_ContentCreated,
    	Event_Handler_ContentDeleted
{
	/**
	 * @param Event_ContentChanged $e
	 */
	public function handleEventContentChanged(Event_ContentChanged $e)
	{
		$this->update($e->Content);
	}
	
	/**
	 * @param Event_ContentCreated $e
	 */
	public function handleEventContentCreated(Event_ContentCreated $e)
	{
		$this->update($e->Content);
	}
	
	/**
	 * @param Event_ContentDeleted $e
	 */
	public function handleEventContentDeleted(Event_ContentDeleted $e)
	{
		$this->set($e->Content, '');
	}
	
	//Interface_Singleton
	const CLASS_NAME = 'Controller_Tags';
	/**
	 * @var Controller_Tags
	 */
	public static $sharedInstance = NULL;
	
	/**
	 * @return Controller_Tags
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
	
	
	/**
	 * Uniform way to convert a string with a bunch of tags in a useful array
	 *
	 * @param string $tagstr
	 */
	public static function parseString($tagstr)
	{
		$tagstr = preg_replace("/[\\r\\n,;]+/u", ";", $tagstr);
		$tagstr = preg_replace("/[\\t\\s]+/u", " ", trim($tagstr));
		$tags = explode(';', $tagstr);
		$tagarr = array();
		foreach ($tags as $tag) 
		{
			$tag = trim($tag);
			if($tag != "")
			{
				$tagarr[] =  $tag;
			}
		}
		$tagarr = array_unique($tagarr);
		return $tagarr;
	}

	public function addTags(array $tags){
		$DB = Core::Database()->createQueryForClass('Controller_Tags');
		foreach ($tags as $tag)
		{
			$DB->call('setTag')
				->withParameters($tag)
				->execute();
		}
	}

	private function setTags($alias, $tagstring)
	{
		$tags = self::parseString($tagstring);
		$DB = Core::Database()->createQueryForClass('Controller_Tags');
		try
		{
		    $DB->beginTransaction();
			$CID = $DB->call('aliasToId')
				->withParameters($alias)
				->fetchSingleValue();

			$DB->call('unlink')
				->withParameters($CID)
				->execute();
			//remove links
			foreach ($tags as $tag) 
			{
				$DB->call('setTag')
					->withParameters($tag)
					->execute();
				$DB->call('linkTag')
					->withParameters($CID, $tag)
					->execute();
			}
			$DB->call('resetStats')->withoutParameters()->execute();
			$DB->call('buildStats')->withoutParameters()->execute();
			$DB->commitTransaction();
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
			echo $e->getTraceAsString();
			$DB->rollbackTransaction();
			return false;
		}
		return true;
	}
	
	private function getTags($alias)
	{
		return Core::Database()
			->createQueryForClass('Controller_Tags')
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
	public function get($ContentOrAlias)
	{
	    $alias = ($ContentOrAlias instanceof Interface_Content)
	        ? $ContentOrAlias->Alias 
	        : $ContentOrAlias;
		return $this->getTags($alias);
	}
}
?>