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
        BObject
    implements 
        Event_Handler_ContentChanged,
        Event_Handler_ContentCreated,
		IShareable
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
    
	public function handleEventContentChanged(Event_ContentChanged $e)
	{
	    try
	    {
	        $CID = $e->Content->Id;
	        $DB = DSQL::getInstance();
	        $DB->beginTransaction();
    	    if(get_class($e->Content) == 'CFeed')
    	    {
    	        //remove all items from feed
				Core::Database()
					->createQueryForClass($this)
					->call('clear')
					->withParameters($CID)
					->execute();
	            //add all matching items for the current filter type
				$type = Core::Database()
					->createQueryForClass($this)
					->call('getType')
					->withParameters($CID)
					->fetchSingleValue();
	            switch($type)
	            {
	                case CFeed::ALL:
	                    SNotificationCenter::report('message','assignItemsUsingAll');
						Core::Database()
							->createQueryForClass($this)
							->call('assignToAll')
							->withParameters($CID, $CID)
							->execute();
	                    break;
	                case CFeed::MATCH_ALL:
	                    SNotificationCenter::report('message','assignItemsUsingMatchAll');
						Core::Database()
							->createQueryForClass($this)
							->call('assignMatchAll')
							->withParameters($CID, $CID, $CID)
							->execute();
	                    break;
	                case CFeed::MATCH_SOME:
	                    SNotificationCenter::report('message','assignItemsUsingMatchSome');
						Core::Database()
							->createQueryForClass($this)
							->call('assignMatchSome')
							->withParameters($CID, $CID)
							->execute();
	                    break;
	                case CFeed::MATCH_NONE:
	                    SNotificationCenter::report('message','assignItemsUsingMatchNone');
	                    Core::Database()
							->createQueryForClass($this)
							->call('assignMatchNone')
							->withParameters($CID, $CID)
							->execute();
	                    break;
	                default:SNotificationCenter::report('warning', 'unknown_type '.$type);
	            }
				$this->updateStats($CID);
    	    }
    	    //feeds may be in other feeds.. so this is for all
	        //remove item from all feeds
			Core::Database()
				->createQueryForClass($this)
				->call('unlink')
				->withParameters($CID)
				->execute();
            $res = Core::Database()
				->createQueryForClass($this)
				->call('feedsWithTypeAndTags')
				->withoutParameters();
            $feedTags = array();
            $feedTypes = array();
            //build array with type string and tag array for each feed
            while($row = $res->fetchResult())
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
            //set feed update time and item count
            foreach ($itemsToAdd as $fid) 
            {
				Core::Database()
					->createQueryForClass($this)
					->call('link')
					->withParameters($fid, $CID)
					->execute();
            	$this->updateStats($CID);
            }
    	    $DB->commit();
	    }
	    catch(Exception $e)
	    {
	        $DB->rollback();
	        throw $e;
	    }
	}
	
	public function handleEventContentCreated(Event_ContentCreated $e)
    {
        try
	    {
	        $CID = $e->Content->Id;
	        $DB = DSQL::getInstance();
	        $DB->beginTransaction();
            if(get_class($e->Content) == 'CFeed')
            {
    	        //set up data in "Feeds"
				Core::Database()
					->createQueryForClass($this)
					->call('setType')
					->withParameters($feedId, CFeed::ALL, CFeed::ALL)
					->execute();
    	        //add all items
				Core::Database()
					->createQueryForClass($this)
					->call('assignToAll')
					->withParameters($CID, $CID)
					->execute();
    	       $this->updateStats($CID);
            }
            else
            {
    	        //check filter for all feeds and add if matching
				$res = Core::Database()
					->createQueryForClass($this)
					->call('feedsWithType')
					->withoutParameters();
	            $feeds = array();
	            while($row = $res->fetchResult())
	            {
	                if($row[1] == CFeed::ALL)
	                {
	                    $feeds[] = $row[0];
	                }
	            }
	            $res->free();
	            foreach ($feeds as $fid) 
	            {
					Core::Database()
						->createQueryForClass($this)
						->call('link')
						->withParameters($fid, $CID)
						->execute();
	            	$this->updateStats($CID);
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

	protected function updateStats($id){
		Core::Database()
			->createQueryForClass($this)
			->call('updateStats')
			->withParameters($id, $id)
			->execute();
	}
}
?>