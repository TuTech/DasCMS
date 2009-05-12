<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class STagPermissions 
    extends 
        BSystem
    implements 
        HContentAccessEventHandler ,
        HWillAccessContentEventHandler  
{
	//IShareable
	const CLASS_NAME = 'STagPermissions';
	/**
	 * @var STagPermissions
	 */
	public static $sharedInstance = NULL;
	
	private static $_permCache = array();
	
	/**
	 * @return STagPermissions
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
    
	/**
	 * @return STagPermissions
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
    
    private function isProtected(BContent $content)
    {
        $protected = false;
        $tags = $content->getTags();
        $res = QSTagPermissions::hasProtectedTags($tags);
        list($count) = $res->fetch();
        $res->free();
        if($count > 0)
        {
            $protected = !$this->checkPermissions($id);
        }
        return $protected;
    }
    
	private function checkPermissions($id)
	{
	    if(!isset(self::$_permCache[$id]))
	    {
	        self::$_permCache[$id] = QSTagPermissions::isPermitted($id, PAuthentication::getUserID());
	    }
	    return self::$_permCache[$id];
	}
	
	/**
	 * before accessing content this event happens
	 * we can substitute content here 
	 *
	 * @param EWillAccessContentEvent $e
	 */
	public function HandleWillAccessContentEvent(EWillAccessContentEvent $e)
	{
	    if($this->isProtected($e->Content))
	    {
	        $e->substitute(CError::Open(401));
	    }
	}

	/**
	 * content that will be displayed
	 * last opportunity to stop protected content 
	 *
	 * @param EContentAccessEvent $e
	 */
	public function HandleContentAccessEvent(EContentAccessEvent $e)
	{
	    if($this->isProtected($e->Content))
	    {
	        $e->Cancel();
	        throw new XPermissionDeniedException('access to content not allowed');
	    }
	}
	
	/**
	 * @return array
	 */
	public static function getProtectedTags()
	{
	    $erg = QSTagPermissions::getProtectedTags();
	    return self::fetchTags($erg);
	}
	
	/**
	 * @return void
	 */
	public static function setProtectedTags(array $tags)
	{
	    QSTagPermissions::setProtectedTags($tags);
	}
	
	/////////
	
	/**
	 * @return void
	 */
	public static function setUserPermissions($name, array $tags)
	{
	    QSTagPermissions::setUserPermissions($name, $tags); 
	}
	
	/**
	 * @return array
	 */
	public static function getUserPermissionTags($name)
	{
	    $erg = QSTagPermissions::getUserPermissionTags($name);
	    return self::fetchTags($erg);
	}
	
	/**
	 * @return void
	 */
	public static function setGroupPermissions($name, array $tags)
	{
	    QSTagPermissions::setGroupPermissions($name, $tags); 
	}
	
	/**
	 * @return array
	 */
	public static function getGroupPermissionTags($name)
	{
	    $erg = QSTagPermissions::getGroupPermissionTags($name);
	    return self::fetchTags($erg);
	}
	
	/////////
	
	/**
	 * @return array
	 */
	private static function fetchTags(DSQLResult $erg)
	{
	    $tags = array();
	    while($row = $erg->fetch())
	    {
	        $tags[] = $row[0];
	    }
	    return $tags;
	}
}
?>