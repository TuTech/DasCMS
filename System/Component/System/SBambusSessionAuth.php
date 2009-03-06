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
    
    private function getAllApps()
    {
		$available = array();
		$appPath = SPath::SYSTEM_APPLICATIONS;
		$dirhdl = opendir($appPath);
		$UAG = SUsersAndGroups::alloc()->init();
		while($item = readdir($dirhdl))
		{
			if(is_dir($appPath.$item) 
				&& substr($item,0,1) != '.' 
				&& strtolower(substr($item,-4)) == '.bap' 
				&& file_exists($appPath.$item.'/Application.xml')
			)
			{
				$available[] = substr($item,0,((strlen(DFileSystem::suffix($item))+1) * -1));
			}
		}
		closedir($dirhdl);
		return $available;
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
                '*.create'      => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Create'),
                '*.delete'      => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Delete'),
                '*.change'      => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Edit'),
                'org.bambuscms.login'=> $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'CMS'),
            );   
            $apps = $this->getAllApps();  
            //hasPermission($victim, $app_name)
            foreach ($apps as $app)
            {
                if($SUsersAndGroups->hasPermission($this->user, $app))
                {
                    $rigths['org.bambusms.application.'.strtolower($app)] = PAuthorisation::PERMIT;
                }
            }
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
        $uag = SUsersAndGroups::alloc()->init();
        if($uag->isValidUser($user, $password))
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
        return ($this->status >= PAuthentication::VALID_USER) 
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
        return ($this->status >= PAuthentication::VALID_USER);
    }
    
}
?>