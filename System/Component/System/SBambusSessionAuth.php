<?php
class SBambusSessionAuth 
    extends 
        BSystem 
    implements 
        IAuthenticate, 
        IAuthorize 
{
    //IAuthorize
    public function getObjectPermissions()
    {
        return array();
    }
    
    public function getGroups()
    {
        $uag = SUsersAndGroups::alloc()->init();
        $g = $uag->listGroupsOfUser(PAuthentication::getUserID(), false);
        if($uag->isMemberOf(PAuthentication::getUserID(), 'Administrator'))
        {
            $g[] = 'Administrator';
        }
        return $g;
    }
    
    public function getPrimaryGroup()
    {
        return SUsersAndGroups::alloc()->init()->getPrimaryGroup(PAuthentication::getUserID());
    }
    
    public function getPermissions()
    {
        $SUsersAndGroups = SUsersAndGroups::alloc()->init();
        if($SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'))
        {
            $rigths = array('*' => PAuthorisation::PERMIT);
        }
        else
        {
            $rigths = array(
                '*'             => PAuthorisation::PERMIT,
                '*.create'      => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Create'),
                '*.delete'      => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Delete'),
                '*.change'      => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Edit'),
                'org.bambuscms.login'=> $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'CMS'),
            );     
        }
        return $rigths;
    }
    
    //IAuthenticate
    
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
        $uag = SUsersAndGroups::alloc()->init();
        if($uag->isValidUser($user, $password))
        {
            $this->user = $user;
            $this->status = PAuthentication::VALID_USER;
        }
        else
        {
            $this->status = (empty($user)) ? (PAuthentication::NO_LOGIN) : (PAuthentication::FAILED_LOGIN) ;
        }
    }  
    
    /**
     * returned value is PAuthentication::FAILED_LOGIN or PAuthentication::NO_LOGIN or PAuthentication::VALID_USER;
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
        return ($this->status == PAuthentication::VALID_USER) 
            ? SUsersAndGroups::alloc()->init()->getRealName($this->user)
            : '';
    }
    
    /**
     * users email address
     * 
     * @return string
     */
    public function getUserEmail()
    {
        return ($this->status == PAuthentication::VALID_USER) 
            ? SUsersAndGroups::alloc()->init()->getEmail($this->user)
            : '';
    }

    /**
     * successful authenticated
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return ($this->status == PAuthentication::VALID_USER);
    }
    
}
?>