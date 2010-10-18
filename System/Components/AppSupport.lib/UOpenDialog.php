<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-06-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class UOpenDialog 
    implements 
        IAjaxAPI,
        IGlobalUniqueId
{
    const GUID = 'org.bambuscms.plugin.uopendialog';
    const CLASS_NAME = 'UOpenDialog';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function isAjaxCallableFunction($function, array $parameterNames)
    {
        return (
            $function == 'getDataForApp'
            && in_array('app', $parameterNames)
        ); 
    }
    
    public function getDataForApp(array $params)
    {
        $ctrl = BAppController::getControllerForID($params['app']);
        if($ctrl instanceof ISupportsOpenDialog)
        {
            //TODO: provide unified content info data from db here
            return $ctrl->provideOpenDialogData(array());
        }
        else
        {
            throw new XInvalidDataException('ofd is not supported');
        }
    }
    
}
?>