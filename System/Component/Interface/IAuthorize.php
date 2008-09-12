<?php
interface IAuthorize
{
    /**
     * return an array with all given permissions for the current user and anonymous
     * @return array 
     */
    public function getPermissions();
}
?>