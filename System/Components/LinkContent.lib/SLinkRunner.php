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
    implements 
        Event_Handler_ContentAccess,
		Interface_Singleton
{
	//Interface_Singleton
	const CLASS_NAME = 'SLinkRunner';
	/**
	 * @var SLinkRunner
	 */
	public static $sharedInstance = NULL;
	/**
	 * @return SLinkRunner
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
	//end Interface_Singleton
    
	public function handleEventContentAccess(Event_ContentAccess $e)
	{
		$content = $e->getContent();
	    if ($content instanceof CLink
	        && $e->getSender() instanceof Controller_View_Content)
	    {
	        //if it is accessed directly - redirect to url
	    	header('Location: '.trim($content->getContent()));
	    }
	    if($content instanceof CLink )
	    {
	        //whoever opened it alter the links content
	        $content->setContent($content->getDescription());
	    }
	}
}
?>