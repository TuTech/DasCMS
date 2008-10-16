<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class SAlias 
    extends 
        BSystem 
    implements 
        IShareable,
		HContentChangedEventHandler, 
		HContentCreatedEventHandler, 
		HContentDeletedEventHandler,
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
		$nice = $this->getUnifiedAlias($content->Title, $content->PubDate);
		
		try
		{
		    $insertAlias = $nice;
		    $dbid = QSAlias::reloveAliasToID($content->Alias);
		    for($i = 1; $i <= 9999; $i++)
		    {
		        if(QSAlias::insertAndCheckAlias($dbid, $insertAlias))
		        {
		            break;
		        }
		        $insertAlias = substr($nice,0,58).'~'.$i;
		    }
		    QSAlias::setActive($insertAlias); 
		}
		catch(Exception $e)
		{
		}
	}	
	
	/**
	 * Get content object by alias
	 *
	 * @param string $alias
	 * @return BContent|null
	 */
	public static function resolve($alias)
	{
		$failed = true;
		$ret = null;
		try
		{
			$res = QSAlias::resolveAlias($alias);	
			if($res->getRowCount() != 1) 
			{
				list($class, $id) = $res->fetch();
				$failed = false;
			}
			$res->free();
		}
		catch(Exception $e)
		{/* checked in if */}
		
		if(!$failed)
		{
    		$sci = SComponentIndex::alloc()->init();
    		if($sci->IsExtension($class, 'BContent'))
    		{
				$ret =  BContent::Open($id);
    		}
		}
		return $ret;
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
	 * check if 2 aliases point to the same content
	 */
	public static function match($alias_a,$alias_b)
	{
	    $res = QSAlias::match($alias_a,$alias_b);
	    if($res->getRowCount() != 1)
	    {
	        return false;
	    }
	    list($content, $count) = $res->fetch();
		return ($count == 2);
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
		return QSAlias::getPrimaryAlias($content->Alias);
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
	const CLASS_NAME = 'SAlias';
	
	public static $sharedInstance = NULL;
	
	/**
	 * Enter description here...
	 *
	 * @return SAlias
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