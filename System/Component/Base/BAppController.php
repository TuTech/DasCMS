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
    implements 
        IGlobalUniqueId 
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
	    
	    //FIXME use some kind of config file
	    switch($appID)
	    {
	        case 'org.bambuscms.applications.websiteeditor':
	            return new AWebsiteEditor();
	        case 'org.bambuscms.applications.templateeditor':
	            return new ATemplateEditor();
	        case 'org.bambuscms.applications.stylesheeteditor':
	            return new AStylesheetEditor();
	        case 'org.bambuscms.applications.treenavigationeditor':
	            return new ATreeNavigationEditor();
	        case 'org.bambuscms.applications.usereditor':
	            return new AUserEditor();
	        case 'org.bambuscms.applications.groupmanager':
	            return new AGroupManager();
	        default:
	            throw new XUndefinedException('controller not found');
	    }
	}
	
	/**
	 * check if this action is permitted for this class
	 *
	 * @param string $action
	 * @return boolean
	 */
	protected function isPermitted($action)
	{
	    return PAuthorisation::has($this->getGUID().'.'.$action);
	}
}
?>