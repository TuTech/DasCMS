<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-01
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
 */
abstract class BAppController 
    extends 
        BObject
{
    protected $target = null;
    
    public function commit(){}
    
    protected static function requirePermission($perm)
    {
        if(!PAuthorisation::has($perm))
        {
            throw new XPermissionDeniedException($perm);
        }
    }  
    
    public function setTarget($target)
    {
        $this->target = $target;
    }
    
    public static function callController(BAppController $controller, $function, array $param)
    {
        if(!empty($function) && method_exists($controller, $function))
        {
            return call_user_func_array(
                array($controller, $function), 
                array($param)
            );
        }
    }
    
    /**
     * array(BContent|string file, [string mimetype])
     * 
     * @return array
     */
    public function getSideBarTarget()
    {
        return array();
    }
    
	/**
	 * load a controller for given app id
	 *
	 * @param string $appID
	 * @return BAppController
	 * @throws XPermissionDeniedException
	 * @throws XUndefinedException
	 */
	public static function getControllerForID($appID)
	{
	    if(!PAuthorisation::has($appID))
	    {
	        throw new XPermissionDeniedException($appID);
	    }
	    $appObject = BObject::InvokeObjectByID($appID);
	    if (!$appObject instanceof BAppController) 
	    {
	    	throw new XUndefinedException('not an app controller');
	    }
	    return $appObject;
	}
	
	public function provideContentLookup(array $namedParameters)
	{
	    return WContentLookup::provideContentLookup($namedParameters);
	}
	
	/**
	 * check if this action is permitted for this class
	 *
	 * @param string $action
	 * @return boolean
	 */
	protected function isPermitted($action)
	{
	    return PAuthorisation::has($this->getClassGUID().'.'.$action);
	}
	
	/**
	 * provide preview image list
	 * @param array $param
	 * @return array
	 */
	public function provideAvailablePreviewImages(array $param)
	{
	    return array(
	    	'renderer' => 'image.php',
	        'scaleHash' => WImage::createScaleHash(128, 96, WImage::MODE_SCALE_TO_MAX),
	        'images' => WImage::getAllPreviewContents()
	    );
	}
}
?>