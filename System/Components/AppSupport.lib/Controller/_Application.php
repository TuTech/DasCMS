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
abstract class _Controller_Application
{
    protected $target = null;
    
    public function commit(){}
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws AccessDeniedException
     * @todo move to _Controller_Application_Content
     */
    public function provideContentTags(array $namedParameters)
    {
        if(!empty($namedParameters['alias']) 
            && Controller_Content::getInstance()->contentExists($namedParameters['alias']))
        {
            return array('tags' => Controller_Tags::getInstance()->get($namedParameters['alias']));
        }
    }
    
    protected static function requirePermission($perm)
    {
        if(!PAuthorisation::has($perm))
        {
            throw new AccessDeniedException($perm);
        }
    }  
    
    public function setTarget($target)
    {
        $this->target = $target;
    }
    
    public static function callController(_Controller_Application $controller, $function, array $param)
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
     * array(Interface_Content|string file, [string mimetype])
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
	 * @return _Controller_Application
	 * @throws AccessDeniedException
	 * @throws Exception
	 */
	public static function getControllerForID($appID)
	{
	    if(!PAuthorisation::has($appID))
	    {
	        throw new AccessDeniedException($appID);
	    }
	    $appObject = BObject::InvokeObjectByID($appID);
	    if (!$appObject instanceof _Controller_Application) 
	    {
	    	throw new Exception(get_class($appObject).' is not an app controller');
	    }
	    return $appObject;
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
	        'scaleHash' => View_UIElement_Image::createScaleHash(128, 96, View_UIElement_Image::MODE_SCALE_TO_MAX),
	        'images' => View_UIElement_Image::getAllPreviewContents()
	    );
	}
}
?>