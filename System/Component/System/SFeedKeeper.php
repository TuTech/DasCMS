<?php
class SFeedKeeper 
    extends 
        BSystem
    implements 
        HContentChangedEventHandler,
        HContentCreatedEventHandler 
{
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
	        //cleanUpFeed
	        //get filter method from content
	        //link
            //commit TX
	    }
	    else
	    {
	        //do content update for all feeds
	        //TX start
	        //cleanUpContent
	        //select feeds
		        //select tags of feed
		        //select filter of feed
	            //update cache
            //commit TX
	    }
	}
	
	public function HandleContentCreatedEvent(EContentCreatedEvent $e)
	{
	   //linkAll 
	}
}
?>