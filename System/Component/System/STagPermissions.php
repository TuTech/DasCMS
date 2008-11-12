<?php
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
	/**
	 * @return STagPermissions
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
	 * @return STagPermissions
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
    
	/**
	 * before accessing content this event happens
	 * we can substitute content here 
	 *
	 * @param EWillAccessContentEvent $e
	 */
	public function HandleWillAccessContentEvent(EWillAccessContentEvent $e)
	{
	    $id = $e->Content->Id;
	    if(!QSTagPermissions::isPermitted($id, PAuthentication::getUserID()))
	    {
	        $e->substitute(CError::Open(403));
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
	    $id = $e->Content->Id;
	    if(!QSTagPermissions::isPermitted($id, PAuthentication::getUserID()))
	    {
	        $e->Cancel();
	        throw new XPermissionDeniedException('access to content not allowed');
	    }
	}
	
	/////////

	public static function getProtectedTags()
	{
	    $erg = QSTagPermissions::getProtectedTags();
	    return self::fetchTags($erg);
	}
	
	public static function setProtectedTags(array $tags)
	{
	    QSTagPermissions::setProtectedTags($tags);
	}
	
	/////////
	
	public static function setUserPermissions($name, array $tags)
	{
	    QSTagPermissions::setUserPermissions($name, $tags); 
	}
	
	public static function getUserPermissionTags($name)
	{
	    $erg = QSTagPermissions::getUserPermissionTags($name);
	    return self::fetchTags($erg);
	}
	
	public static function setGroupPermissions($name, array $tags)
	{
	    QSTagPermissions::setGroupPermissions($name, $tags); 
	}
	
	public static function getGroupPermissionTags($name)
	{
	    $erg = QSTagPermissions::getGroupPermissionTags($name);
	    return self::fetchTags($erg);
	}
	
	/////////
	
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