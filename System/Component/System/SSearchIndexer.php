<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-22
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SSearchIndexer 
    extends 
        BSystem
    implements 
        HContentChangedEventHandler,
        HContentDeletedEventHandler,
        HContentRevokedEventHandler,
        HContentPublishedEventHandler
{
	//IShareable
	const CLASS_NAME = 'SSearchIndexer';
	/**
	 * @var SSearchIndexer
	 */
	public static $sharedInstance = NULL;
	/**
	 * @return SSearchIndexer
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
    
    public static function extractFeatures($text)
    {
        if(is_array($text))
        {
            $newText = '';
            $index = array();
            foreach ($text as $k => $v)
            {
                if(!is_array($v))
                {
                    $newText .= ' '.$v;
                }
            }
            $text = $newText;
        }
        
        $text = strval($text);
        $text = strtolower(strip_tags($text));
        $text = html_entity_decode($text, ENT_QUOTES, CHARSET);
        $text = str_replace('&nbsp;', ' ', $text);
        $text = preg_replace('/[\s\_\-\/\n\t\r]+/mui', ' ', $text);
        $text = str_replace(';', ' ', $text);
        $text = htmlentities($text, ENT_NOQUOTES, CHARSET);
        $text = str_replace('&', ' ', $text);
        $text = str_replace(';', ' ', $text);
        $words = preg_split('/\b/mui',$text);
        $index = array();
        foreach ($words as $word)
        {
            $strip = '\s\'"?!\.:,;\-_´`\(\)=<>';
            $word = preg_replace('/^['.$strip.']*/mui', '', $word);
            $word = preg_replace('/['.$strip.']*$/mui', '', $word);
            if(!empty($word) && strlen($word) > 1)
            {
                $word = substr($word,0,48);
                if(!isset($index[$word]))
                {
                    $index[$word] = 0;
                }
                $index[$word]++;
            }
        }
        return $index;
    }
    
	public function HandleContentChangedEvent(EContentChangedEvent $e)
	{
	    if($e->Content->getPubDate() > 0)
	    {
	        $this->schduleIndexing($e->Content->getId());
	    }
	}
	
	public function HandleContentPublishedEvent(EContentPublishedEvent  $e)
	{
        $this->schduleIndexing($e->Content->getId());
	}
	
	public function HandleContentDeletedEvent(EContentDeletedEvent $e)
    {
        $this->removeFromIndex($e->Content->getId());
	}
	
	public function HandleContentRevokedEvent(EContentRevokedEvent $e)
	{
	    $this->removeFromIndex($e->Content->getId());
	}
	
	
	private function schduleIndexing($contentID)
	{
	    SNotificationCenter::report('message', 'schedule_indexing');
	    QSSearchIndexer::scheduleUpdate($contentID);
	}
	
	private function removeFromIndex($contentID)
	{
	    SNotificationCenter::report('message', 'removing_form_index');
	    QSSearchIndexer::removePendingUpdate($contentID);
	    QSSearchIndexer::removeIndex($contentID);
	}
	
	/**
	 * @return BContent|null
	 */
	public static function nextToUpdate()
	{
	    $res = QSSearchIndexer::getNetToUpdate();
	    $content = null;
	    if($res->getRowCount())
	    {
	        list($alias) = $res->fetch();
	        $content = Controller_Content::getSharedInstance()->openContent($alias);
	    }
	    return $content;
	}
	/**
	 * @param BContent $content
	 * @return void
	 */
	public static function updateFeatures(BContent $content)
	{
	    $DB = DSQL::getSharedInstance();
	    //$DB->beginTransaction();
	    echo '.';
	    try
	    {
	        QSSearchIndexer::removePendingUpdate($content->getId());
    	    QSSearchIndexer::removeIndex($content->getId());
    	    $exclude = array();
    	    if($content instanceof ISearchDirectives)
    	    {
    	        if(!$content->allowSearchIndex())
    	        {
        	        return;
    	        }
    	        else
    	        {
    	            $exclude = $content->excludeAttributesFromSearchIndex();
    	            if(!is_array($exclude))
    	            {
    	                $exclude = array();
    	            }
    	        }
    	    }
    	    echo '.';
    	    $res = QSSearchIndexer::getAttributes();
    	    $atts = array();
    	    while ($row = $res->fetch())
    	    {
    	        $atts[$row[0]] = $row[1];
    	    }
    	    $res->free();
    	    echo '.';
    	    
    	    foreach ($atts as $id => $att)
    	    {
                if(isset($content->{$att}) && !in_array($att, $exclude))
                {
                    $features = self::extractFeatures($content->{$att});
                    QSSearchIndexer::dumpFeatures(array_keys($features));
                    QSSearchIndexer::linkContentAttributeFeatures($content->getId(), $id, $features);
                }
    	    }
    	    echo '.';
    	    //$DB->commit();
    	    echo '<h1>finished</h1>';
	    }
	    catch (XDatabaseException $e)
	    {
	        //$e->rollback();
	        echo '<b>', $e->getSQL(),'</b>';
	        throw $e;
	    }
	}	
}
?>