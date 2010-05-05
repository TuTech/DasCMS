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
        BObject 
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
        return PAuthentication::isAuthenticated() ? array('Administrator') : array();
    }
    
    public function getPrimaryGroup()
    {
        return PAuthentication::isAuthenticated() ? 'Administrator' : null;
    }
    
    public function getPermissions()
    {
        return PAuthentication::isAuthenticated() ? array('*' => PAuthorisation::PERMIT): array();
    }
    
    public function getRole()
    {
        return PAuthorisation::ROLE_ADMINISTRATOR;
    }
}
?>