<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SLinkRunner 
    extends 
        BSystem
    implements 
        HContentAccessEventHandler
{
	//IShareable
	const CLASS_NAME = 'SLinkRunner';
	/**
	 * @var SLinkRunner
	 */
	public static $sharedInstance = NULL;
	/**
	 * @return SLinkRunner
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
	 * @return SLinkRunner
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
    
	public function HandleContentAccessEvent(EContentAccessEvent $e)
	{
	    if ($e->Content instanceof CLink 
	        && !$e->Sender instanceof WImage) 
	    {
	    	header('Location: '.$e->Content->Content);
	    	exit();
	    }
	}
}
?>