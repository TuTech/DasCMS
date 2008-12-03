<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 01.10.2008
 * @license GNU General Public License 3
 */
abstract class BAppController 
    extends 
        BObject
{
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
}
?>