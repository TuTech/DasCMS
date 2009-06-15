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
class UContentLookup
    extends BPlugin 
    implements 
        IAjaxAPI,
        IGlobalUniqueId
{
    const GUID = 'org.bambuscms.plugin.ucontentlookup';
    const CLASS_NAME = 'UContentLookup';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function isAjaxCallableFunction($function, array $parameterNames)
    {
        return $function == 'lookup'; 
    }
    
    public function lookup(array $params)
    {
    }
    
}
?>