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

    private static $accessedContents = array();
    
    public function HandleContentAccessEvent(EContentAccessEvent $e)
    {
        self::$accessedContents[$e->Content->Id] = $e;
    }
    
    public static function accessedContent()
    {
        return self::$accessedContents;
    }
    
	/**
	 * before accessing content this event happens
	 * we can substitute content here 
	 *
	 * @param EWillAccessContentEvent $e
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