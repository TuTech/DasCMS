<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-10
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SAuthByPhpAuthUser 
    extends 
        BSystem 
    implements 
        IAuthenticate
{
    const NAME = 'trust_php_auth_user';
    //IAuthenticate
    
    private $user = '';
    private $status;
    private $attemptedUserID = '';
    /**
     * try authentication 
     * @return void
     */
    public function authenticate()
    {
        global $_SERVER;
        $user = '';
        $password = '';
        if(!empty($_SERVER["PHP_AUTH_USER"] ))
        {
            $user = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
        }
        $this->attemptedUserID = $user;
        //check login data
        if(!empty($user))
        {
            $this->user = $user;
            $this->status = PAuthentication::VALID_USER;
        }
        else
        {
            $this->status = PAuthentication::NO_LOGIN;
        }
    }  
    
    /**
     * returned value is PAuthentication::FAILED_LOGIN or PAuthentication::NO_LOGIN or PAuthentication::VALID_USER or PAuthentication::CONTINUED_SESSION;
     * 
     * @return int
     */
    public function getAuthenticationState()
    {
        return $this->status;
    }

    /**
     * user login name
     * @return string
     */
    public function getUserID()
    {
        return $this->user;
    }
    
    /**
     * user login name
     * @return string
     */
    public function getAttemptedUserID()
    {
        return $this->attemptedUserID;
    }
    
    /**
     * users real name
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->user;
    }
    
    /**
     * users email address
     * 
     * @return string
     */
    public function getUserEmail()
    {
        return '';
    }

    /**
     * successful authenticated
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return ($this->status >= PAuthentication::VALID_USER);
    }
    
}
?>