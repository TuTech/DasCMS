<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-10
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
 */
interface IAuthorize
{
    //const NAME;
    /**
     * return an array with all given permissions for the current user 
     * permission => (PAuthorisation::PERMIT | PAuthorisation::DENY)
     * @return array 
     */
    public function getPermissions();
    
    /**
     * return an array with all given object permissions for the current user 
     * permission => objectID => (PAuthorisation::PERMIT | PAuthorisation::DENY)
     * @return array 
     */
    public function getObjectPermissions();
    public function getGroups();
    public function getPrimaryGroup();
}
?>