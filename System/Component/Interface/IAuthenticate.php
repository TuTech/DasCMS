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
interface IAuthenticate
{
    /**
     * try authentication 
     * @return void
     */
    public function authenticate();
    
    /**
     * returned value is PAuthentication::FAILED_LOGIN or PAuthentication::NO_LOGIN or PAuthentication::VALID_USER or PAuthentication::CONTINUED_SESSION;
     * 
     * @return int
     */
    public function getAuthenticationState();

    /**
     * user login name
     * @return string
     */
    public function getUserID();
    
    /**
     * user login name
     * @return string
     */
    public function getAttemptedUserID();
    
    /**
     * users real name
     *
     * @return string
     */
    public function getUserName();
    
    /**
     * users email address
     * 
     * @return string
     */
    public function getUserEmail();

    /**
     * successful authenticated
     *
     * @return boolean
     */
    public function isAuthenticated();
    
}
?>