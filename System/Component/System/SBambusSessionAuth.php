<?php
class SBambusSessionAuth implements IAuthenticate, IAuthorize 
{
    //IAuthorize
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
                '*.view'        => PAuthorisation::PERMIT,
                '*.create'      => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Create'),
                '*.delete'      => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Delete'),
                '*.change'      => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Edit'),
                'org.bambus-cms'=> $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'CMS'),
                
                //compat
                'org.bambus-cms.configuration.view.*'   => PAuthorisation::PERMIT,
                'org.bambus-cms.configuration.change.*' => PAuthorisation::PERMIT,
                'org.bambus-cms.layout.*'               => PAuthorisation::PERMIT,
                'org.bambus-cms.content.*'              => PAuthorisation::PERMIT,
                'org.bambus-cms.credentials.*'          => PAuthorisation::PERMIT
            );     
           // $applications = $SUsersAndGroups->      
        }
        return $rigths;
    }
    
    //IAuthenticate
    
    private $user;
    private $status;
    /**
     * try authentication 
     * @return void
     */
    public function authenticate()
    {
        $user = '';
        $password = '';
        if(RSession::hasValue('bambus_cms_username'))
        {
            $user = RSession::get('bambus_cms_username');
            $password = RSession::get('bambus_cms_password');
        }
        elseif(RSent::hasValue('bambus_cms_username'))
        {
            $user = RSent::get('bambus_cms_username');
            $password = RSent::get('bambus_cms_password');
            RSession::set('bambus_cms_username', $user);
            RSession::set('bambus_cms_password', $password);
        }
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