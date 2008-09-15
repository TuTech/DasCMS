<?php
interface IAuthorize
{
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