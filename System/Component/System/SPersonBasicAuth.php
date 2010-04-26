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
class SPersonBasicAuth 
    extends 
        BSystem 
    implements 
        IAuthenticate
{
    const NAME = 'person_basic_authentication';
    //IAuthenticate
    /**
     * @var CPerson
     */
    private $person = null;
    private $user;
    private $status;
    private $attemptedUserID = '';
    /**
     * try authentication 
     * @return void
     */
    public function authenticate()
    {
        $user = '';
        $password = '';
        $newLogin = false;
        if(!RURL::has('_destroy_session') && !RURL::has('_bambus_logout'))
        {
            //there might be something useful in here
            if(!RSession::has('bambus_cms_username') && !empty($_SERVER['PHP_AUTH_USER']))
            {
                $user = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];
            }            
            //this has priority
            if(RSent::hasValue('bambus_cms_username'))
            {
                $user = RSent::get('bambus_cms_username');
                $password = RSent::get('bambus_cms_password');
            }
            //save user name and password
            if(!empty($user))
            {
                $newLogin = true;
                RSession::reset();
                RSession::set('bambus_cms_username', $user);
                RSession::set('bambus_cms_password', $password);
            }
            //get username and password for later use
            if(RSession::hasValue('bambus_cms_username'))
            {
                $user = RSession::get('bambus_cms_username');
                $password = RSession::get('bambus_cms_password');
            }
        }
        $this->attemptedUserID = $user;
        //check login data
        if(CPerson::isUser($user))
        {
            $person = CPerson::getPersonForLogin($user);
            if($person->validatePassword($password))
            {
                $this->person = $person;
                //SNotificationCenter::report('message', 'ok');
            }
            else
            {
                //SNotificationCenter::report('message', 'invalid_password');
            }
        }
        else
        {
            //SNotificationCenter::report('message', 'not_a_user');
        }
        if($this->person != null)
        {
            $this->user = $user;
            $this->status = $newLogin ? (PAuthentication::VALID_USER) : (PAuthentication::CONTINUED_SESSION);
        }
        else
        {
            $this->status = (empty($user)) ? (PAuthentication::NO_LOGIN) : (PAuthentication::FAILED_LOGIN) ;
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
        return ($this->status >= PAuthentication::VALID_USER) 
            ? $this->person->getTitle()
            : '';
    }
    
    /**
     * users email address
     * 
     * @return string
     */
    public function getUserEmail()
    {
        return ($this->status >= PAuthentication::VALID_USER) 
            ? ''
            : '';
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