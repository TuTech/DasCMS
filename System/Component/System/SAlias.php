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
		//@todo IAliasBuilder
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
	 * @deprecated for NTreeNavigation helper only
	 * @param string $alias
	 * @param array $aliasesToMatch
	 * @return string
	 */
	public static function getMatching($alias, array $aliasesToMatch)
	{
	    if(count($aliasesToMatch) == 0)
	    {
	        return false;
	    }
	    $res = QSAlias::getMatching($alias, $aliasesToMatch);
	    if($res->getRowCount() != 1)
	    {
	        return null;
	    }
	    list($match) = $res->fetch();
		return $match;
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