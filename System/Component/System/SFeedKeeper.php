<?php
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
	 * @return SContentIndex
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
    
	public function HandleContentChangedEvent(EContentChangedEvent $e)
	{
	    try
	    {
	        $CID = $e->Content->Id;
	        $DB = DSQL::alloc()->init();
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
	                    QSFeedKeeper::assignItemsUsingAll($CID);
	                    break;
	                case CFeed::MATCH_ALL:
	                    QSFeedKeeper::assignItemsUsingMatchAll($CID);
	                    break;
	                case CFeed::MATCH_SOME:
	                    QSFeedKeeper::assignItemsUsingMatchSome($CID);
	                    break;
	                case CFeed::MATCH_NONE:
	                    QSFeedKeeper::assignItemsUsingMatchNone($CID);
	                    break;
	            }
	            QSFeedKeeper::updateStats($CID);
    	    }
    	    else
    	    {
    	        //remove item from all feeds
	            QSFeedKeeper::unlinkItem($CID);
	            $res = QSFeedKeeper::getFeedsWithTypeAndTags();
	            $feedTags = array();
	            $feedTypes = array();
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
	                if($type != CFeed::ALL)
	                {
	                    $matching = array_intersect($e->Content->Tags, $feedTags[$fid]);
	                }
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
	            if(count($itemsToAdd) > 0)
	            {
	                QSFeedKeeper::linkItem($CID, $itemsToAdd);
	            }
	            //set feed update time and item count
	            foreach ($itemsToAdd as $fid) 
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
	
	public function HandleContentCreatedEvent(EContentCreatedEvent $e)
    {
        try
	    {
	        $CID = $e->Content->Id;
	        $DB = DSQL::alloc()->init();
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