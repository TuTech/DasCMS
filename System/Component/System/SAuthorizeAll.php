<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-11
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SAuthorizeAll 
    extends 
        BSystem 
    implements 
        IAuthorize 
{
    const NAME = 'disable_permissions';
    
    //IAuthorize
    public function getObjectPermissions()
    {
        return array();
    }
    
    public function getGroups()
    {
        $uid = PAuthentication::getUserID();
        return !empty($uid) ? array('Administrator') : array();
    }
    
    public function getPrimaryGroup()
    {
        return null;
    }
    
    public function getPermissions()
    {
        $uid = PAuthentication::getUserID();
        return !empty($uid) ? array('*' => PAuthorisation::PERMIT): array();
    }
    
    public function getRole()
    {
        return PAuthorisation::ROLE_ADMINISTRATOR;
    }
}
?>