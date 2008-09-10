<?php
interface IAuthenticate
{
    /**
     * try authentication 
     * @return void
     */
    public function authenticate();
    
    /**
     * returned value is PAuthentication::FAILED_LOGIN or PAuthentication::NO_LOGIN or PAuthentication::VALID_USER;
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