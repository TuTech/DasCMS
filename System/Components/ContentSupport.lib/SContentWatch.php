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
    extends BObject
    implements 
        Event_Handler_ContentAccess,
        Event_Handler_WillAccessContent,
		Event_Handler_WillSendHeaders,
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
    
    public static function accessedContent()
    {
        return self::$accessedContents;
    }
    
    private static $accessedContents = array();
    
    /**
     * @param Event_WillSendHeaders $e
     */
	public function handleEventWillSendHeaders(Event_WillSendHeaders $e) 
    {
        $feeds = array();
        $tags = array();
        $descriptions = array();
        $cdesc = Core::settings()->get('meta_description');
        if(!empty($cdesc))
        {
            $descriptions[] = $cdesc;
        }
        $titles = array();
        foreach (self::$accessedContents as $id => $event)
        {
            $content = $event->Content;
			$allowFeed = true;
			$allowTitle = true;
			$allowDescription = true;
			$noHeaders = false;
            foreach ($content->getTags() as $tag){
				$t = strtolower($tag);
				if($t == '@noheaders' || $t == '@noheader'){
					$noHeaders = true;
				}
				else{
					$allowFeed = $allowFeed && $t != '@nofeed';
					$allowTitle = $allowTitle && $t != '@notitle';
					$allowDescription = $allowDescription && $t != '@nodesc';
					$allowDescription = $allowDescription && $t != '@nodescription';
				}
			}
			if($noHeaders){
				continue;
			}
            //Atom feeds
            if($allowFeed && $content instanceof IGeneratesFeed && $content->getLinkToFeed($content->Alias) != null)
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
            if($content instanceof IContentHeaders)
            {
                $content->sendContentHeaders($e->getHeader());
            }
            
            if($event->Sender instanceof BView)
            {
                if($event->Sender->publishMetaData())
                {
                    //if !view->silent
                    if(is_array($content->Tags))
                    {
                        $tags = array_merge($tags, $content->Tags);
                    }
                    if($allowDescription && trim($content->Description) != '')
                    {
                        $descriptions[] = $content->Description;
                    }
                    if($allowTitle && trim($content->Title) != '')
                    {
                        $titles[] = $content->Title;
                    }
                }
            }
        }
        $ctags = STag::parseTagStr(Core::settings()->get('meta_keywords'));
        $tags = array_merge($ctags, $tags);
        $tags = array_unique($tags);
		$visibleTags = array();
		foreach ($tags as $tag){
			if(substr($tag,0,1) != '@'){
				$visibleTags[] = $tag;
			}
		}
        if(count($visibleTags) > 0)
        {
            $e->getHeader()->addMeta(implode(', ', $visibleTags), 'keywords');
        }
        $title = Core::settings()->get('sitename');
        if(count($titles) > 0)
        {
            $titles = array_unique($titles);
            $title .= implode(', ', $titles);
        }
        $e->getHeader()->setTitle($title);
        if(count($descriptions) > 0)
        {
            $desc = implode(', ', $descriptions);
            $desc = html_entity_decode($desc, ENT_QUOTES, CHARSET);
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
     * @param Event_ContentAccess $e
     */
    public function handleEventContentAccess(Event_ContentAccess $e)
    {
        //logging
        $o = $e->Content;
	    if($e->Sender instanceof BView 
	        && !array_key_exists($o->getId(), self::$accessedContents))
	    {
    	    //country
    	    $ccid = 0;
    	    if(function_exists('geoip_country_code_by_name'))
    	    {
    	        $cc = geoip_country_code_by_name($_SERVER['REMOTE_ADDR']);
    	        if(strlen($cc) == 2)
    	        {
    	            $ccid = ord(substr($cc,0,1))*256+ord(substr($cc,1,1));
    	        }
    	    }
    	    //ip addr
    	    list($a, $b, $c, $d) = explode('.', $_SERVER['REMOTE_ADDR']);
            $num = (sprintf('0x%02x%02x%02x%02x',$a, $b, $c, $d));
            $num = hexdec($num);//FIXME anon here
            //send to db
            if(!$o instanceof CError && Core::settings()->get('log_page_accesses') != '')
            {
				Core::Database()
					->createQueryForClass($this)
					->call('log')
					->withParameters($o->getId(), $ccid, $num)
					->execute();
            }
	    }
        self::$accessedContents[$e->Content->Id] = $e;
    }
    
    
	/**
	 * before accessing content this event happens
	 * we can substitute content here 
	 *
	 * @param Event_WillAccessContent $e
     */
	public function handleEventWillAccessContent(Event_WillAccessContent $e)
	{
	    $pubDate = $e->Content->getPubDate();
	    if(empty($pubDate) || $pubDate > time())
	    {
	        $e->substitute(new CError(403));
	    }
	}
}
?>