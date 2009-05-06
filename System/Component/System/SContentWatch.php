<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-31
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SContentWatch 
    extends BSystem
    implements 
        HContentAccessEventHandler,
        HWillAccessContentEventHandler,
        HWillSendHeadersEventHandler,
        IShareable   
{
	//IShareable
	const CLASS_NAME = 'SContentWatch';
	/**
	 * @var SContentWatch
	 */
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @return SContentWatch
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
	 * @return SContentWatch
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
    
    public static function accessedContent()
    {
        return self::$accessedContents;
    }
    
    private static $accessedContents = array();
    
    /**
     * @param EWillSendHeadersEvent $e
     * (non-PHPdoc)
     * @see System/Component/EventHandler/HWillSendHeadersEventHandler#HandleWillSendHeadersEvent()
     */
    public function HandleWillSendHeadersEvent(EWillSendHeadersEvent $e)
    {
        $feeds = array();
        $tags = array();
        $descriptions = array();
        $titles = array();
        foreach (self::$accessedContents as $id => $event)
        {
            $content = $event->Content;
            
            //Atom feeds
            if($content instanceof IGeneratesFeed && $content->getLinkToFeed($content->Alias) != null)
            {
                $e->getHeader()->addLink(
                    null,    
                    $content->getLinkToFeed($content->Alias),
                    null,
                    'application/atom+xml', 
                    $content->Title, 
                    'alternate'
                );
            }
            
            if($event->Sender instanceof BView)
            {
                if($event->Sender->publishMetaData())
                {
                    //if !view->silent
                    $tags = array_merge($tags, $content->Tags);
                    if(trim($content->Description) != '')
                    {
                        $descriptions[] = $content->Description;
                    }
                    if(trim($content->Title) != '')
                    {
                        $titles[] = $content->Title;
                    }
                }
            }
        }
        $tags = array_unique($tags);
        if(count($tags) > 0)
        {
            $e->getHeader()->addMeta(implode(', ', $tags), 'keywords');
        }
        $title = LConfiguration::get('sitename');
        if(count($titles) > 0)
        {
            $title .= implode(', ', $titles);
        }
        $e->getHeader()->setTitle($title);
        if(count($descriptions) > 0)
        {
            $desc = implode(', ', $descriptions);
            $desc = strip_tags($desc);
            $desc = preg_replace('/\s+/mui', ' ', $desc);
            if(strlen($desc) > 255)
            {
                $desc = substr($desc,0,252).'...';
            }
            $e->getHeader()->addMeta($desc, 'description');
        }
    }
    
    /**
     * @param EContentAccessEvent $e
     * (non-PHPdoc)
     * @see System/Component/EventHandler/HContentAccessEventHandler#HandleContentAccessEvent()
     */
    public function HandleContentAccessEvent(EContentAccessEvent $e)
    {
        self::$accessedContents[$e->Content->Id] = $e;
    }
    
    
	/**
	 * before accessing content this event happens
	 * we can substitute content here 
	 *
	 * @param EWillAccessContentEvent $e
     * (non-PHPdoc)
     * @see System/Component/EventHandler/HWillAccessContentEventHandler#HandleWillAccessContentEvent()
     */
	public function HandleWillAccessContentEvent(EWillAccessContentEvent $e)
	{
	    $pubDate = $e->Content->getPubDate();
	    if(empty($pubDate) || $pubDate > time())
	    {
	        $e->substitute(CError::Open(403));
	    }
	}
}
?>