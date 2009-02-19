<?php
class SContentWatch 
    extends BSystem
    implements 
        HContentAccessEventHandler,
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
}
?>