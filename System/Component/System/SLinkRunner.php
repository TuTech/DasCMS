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
    
	public function HandleContentAccessEvent(EContentAccessEvent $e)
	{
	    if ($e->Content instanceof CLink 
	        && $e->Sender instanceof VSpore) 
	    {
	        //if it is accessed directly - redirect to url
	    	header('Location: '.$e->Content->Content);
	    }
	    if($e->Content instanceof CLink )
	    {
	        //whoever opened it alter the links content
	        $e->Content->setContent($e->Content->Description);
	    }
	}
}
?>