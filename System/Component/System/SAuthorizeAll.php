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
        return CPerson::isUser(PAuthentication::getUserID()) ? array('Administrator') : array();
    }
    
    public function getPrimaryGroup()
    {
        return null;
    }
    
    public function getPermissions()
    {
        return CPerson::isUser(PAuthentication::getUserID()) ? array('*' => PAuthorisation::PERMIT): array();
    }
    
}
?>