<?php
class SFeedKeeper 
    extends 
        BSystem
    implements 
        HContentChangedEventHandler,
        HContentCreatedEventHandler 
{
    const ALL = 'All';
    const MATCH_SOME = 'MatchSome';
    const MATCH_ALL = 'MatchAll';
    const MATCH_NONE = 'MatchNone';
    
    //cleanUpFeed
	//cleanUpContent
	//linkAll
	//linkMatchingAllTags
	//linkMatchingSomeTags
	//linkNotMatchingAnyTags

	public function HandleContentChangedEvent(EContentChangedEvent $e)
	{
	    if(get_class($e->Content) == 'CFeed')
	    {
	        //do feed update
            //TX start
            //QSFeedKeeper::unlinkFeed(feed);
            //QSFeedKeeper::getFeedType(feed)
            //QSFeedKeeper::assignItemsUsing<filterType>(feed)
            //commit TX
	    }
	    else
	    {
	        //do content update for all feeds
	        //TX start
	        //QSFeedKeeper::unlinkItem(item);
	        //QSFeedKeeper::getFeedsWithTypeAndTags()
	        //QSFeedKeeper::linkItem(item, feeds[]);
	        //QSFeedKeeper::updateStats(feed)
            //commit TX
	    }
	}
	
	public function HandleContentCreatedEvent(EContentCreatedEvent $e)
    {
        if(get_class($e->Content) == 'CFeed')
        {
	        //set up data in Feeds
	        //build linking
            //QSFeedKeeper::setFeedType(feed, filterType)
            //QSFeedKeeper::assignItemsUsing<filterType>(feed)
            //QSFeedKeeper::updateStats(feed)
        }
        else
        {
	        //check filter for all feeds and add if matching
	        //QSFeedKeeper::getFeedsWithType()
	        //QSFeedKeeper::linkItem(item, feeds[]);
	        //QSFeedKeeper::updateStats(feed)
        }
	}
}
?>