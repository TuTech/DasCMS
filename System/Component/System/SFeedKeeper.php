<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-21
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SFeedKeeper 
    extends 
        BSystem
    implements 
        HContentChangedEventHandler,
        HContentCreatedEventHandler 
{
	//IShareable
	const CLASS_NAME = 'SFeedKeeper';
	/**
	 * @var SFeedKeeper
	 */
	public static $sharedInstance = NULL;
	/**
	 * @return SFeedKeeper
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
    
	public function HandleContentChangedEvent(EContentChangedEvent $e)
	{
	    try
	    {
	        $CID = $e->Content->Id;
	        $DB = DSQL::getSharedInstance();
	        $DB->beginTransaction();
    	    if(get_class($e->Content) == 'CFeed')
    	    {
    	        //remove all items from feed
	            QSFeedKeeper::clearFeed($CID);
	            //add all matching items for the current filter type
	            $res = QSFeedKeeper::getFeedType($CID);
	            list($type) = $res->fetch();
	            $res->free();
	            switch($type)
	            {
	                case CFeed::ALL:
	                    SNotificationCenter::report('message','assignItemsUsingAll');
	                    QSFeedKeeper::assignItemsUsingAll($CID);
	                    break;
	                case CFeed::MATCH_ALL:
	                    SNotificationCenter::report('message','assignItemsUsingMatchAll');
	                    QSFeedKeeper::assignItemsUsingMatchAll($CID);
	                    break;
	                case CFeed::MATCH_SOME:
	                    SNotificationCenter::report('message','assignItemsUsingMatchSome');
	                    QSFeedKeeper::assignItemsUsingMatchSome($CID);
	                    break;
	                case CFeed::MATCH_NONE:
	                    SNotificationCenter::report('message','assignItemsUsingMatchNone');
	                    QSFeedKeeper::assignItemsUsingMatchNone($CID);
	                    break;
	                default:SNotificationCenter::report('warning', 'unknown_type '.$type);
	            }
	            QSFeedKeeper::updateStats($CID);
    	    }
    	    //feeds may be in other feeds.. so this is for all
	        //remove item from all feeds
            QSFeedKeeper::unlinkItem($CID);
            $res = QSFeedKeeper::getFeedsWithTypeAndTags();
            $feedTags = array();
            $feedTypes = array();
            //build array with type string and tag array for each feed
            while($row = $res->fetch())
            {
                list($fid, $type, $tag) = $row;
                if(!isset($feedTags[$fid]))
                {
                    $feedTags[$fid] = array();
                }
                $feedTags[$fid][] = $tag;
                $feedTypes[$fid] = $type;
            }
            $res->free();
            //add item to all feeds with matching filter
            $itemsToAdd = array();
            foreach ($feedTypes as $fid => $type)
            {
                if($type == CFeed::ALL)
                {
                    $itemsToAdd[] = $fid;
                }
                else
                {
                    $matching = array_intersect($e->Content->Tags, $feedTags[$fid]);
    	            switch($type)
    	            {
    	                case CFeed::ALL:
    	                    $match = true;
    	                    break;
    	                case CFeed::MATCH_ALL:
    	                    $match = count($matching) == count($feedTags[$fid]);
    	                    break;
    	                case CFeed::MATCH_SOME:
    	                    $match = count($matching) >= 1;
    	                    break;
    	                case CFeed::MATCH_NONE:
    	                    $match = count($matching) == 0;
    	                    break;
    	            }  
    	            if($match)
    	            {
    	                $itemsToAdd[] = $fid;
    	            }
                }
            }
            //add this item to all matching feeds
            if(count($itemsToAdd) > 0)
            {
                QSFeedKeeper::linkItem($CID, $itemsToAdd);
            }
            //set feed update time and item count
            foreach ($itemsToAdd as $fid) 
            {
            	QSFeedKeeper::updateStats($fid);
            }
    	    $DB->commit();
	    }
	    catch(Exception $e)
	    {
	        $DB->rollback();
	        throw $e;
	    }
	}
	
	public function HandleContentCreatedEvent(EContentCreatedEvent $e)
    {
        try
	    {
	        $CID = $e->Content->Id;
	        $DB = DSQL::getSharedInstance();
	        $DB->beginTransaction();
            if(get_class($e->Content) == 'CFeed')
            {
    	        //set up data in "Feeds"
    	        QSFeedKeeper::setFeedType($CID, CFeed::ALL);
    	        //add all items
    	        QSFeedKeeper::assignItemsUsingAll($CID);
    	        QSFeedKeeper::updateStats($CID);
            }
            else
            {
    	        //check filter for all feeds and add if matching
    	        $res = QSFeedKeeper::getFeedsWithType();
	            $feeds = array();
	            while($row = $res->fetch())
	            {
	                if($row[1] == CFeed::ALL)
	                {
	                    $feeds[] = $row[0];
	                }
	            }
	            $res->free();
	            if(count($feeds) > 0)
	            {
	                QSFeedKeeper::linkItem($CID, $feeds);
	            }
	            foreach ($feeds as $fid) 
	            {
	            	QSFeedKeeper::updateStats($fid);
	            }
            }
    	    $DB->commit();
	    }
	    catch(Exception $e)
	    {
	        $DB->rollback();
	        throw $e;
	    }
	}
}
?>