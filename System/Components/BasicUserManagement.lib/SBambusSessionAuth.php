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
class SBambusSessionAuth 
    implements 
        IAuthenticate, 
        IAuthorize 
{
    const NAME = 'old_user_and_group_system';
    
    //IAuthorize
    public function getObjectPermissions()
    {
        return array();
    }
    
    public function getGroups()
    {
        $uag = SUsersAndGroups::getInstance();
        $g = $uag->listGroupsOfUser(PAuthentication::getUserID(), false);
        if($uag->isMemberOf(PAuthentication::getUserID(), 'Administrator'))
        {
            $g[] = 'Administrator';
        }
        return $g;
    }
    
    public function getPrimaryGroup()
    {
        return SUsersAndGroups::getInstance()->getPrimaryGroup(PAuthentication::getUserID());
    }
    
    private function getAllApps()
    {
		$available = array();
		$appPath = Core::PATH_SYSTEM_APPLICATIONS;
		$dirhdl = opendir($appPath);
		$UAG = SUsersAndGroups::getInstance();
		while($item = readdir($dirhdl))
		{
			if(is_dir($appPath.$item) 
				&& substr($item,0,1) != '.' 
				&& strtolower(substr($item,-4)) == '.bap' 
				&& file_exists($appPath.$item.'/Application.xml')
			)
			{
				$available[] = substr($item,0,((strlen(Core::FileSystem()->suffix($item))+1) * -1));
			}
		}
		closedir($dirhdl);
		return $available;
    }
    
    public function getPermissions()
    {
        $SUsersAndGroups = SUsersAndGroups::getInstance();
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
                '*.view'        => $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'CMS'),
                'org.bambuscms.login'=> $SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'CMS'),
            );   
            $apps = $this->getAllApps();  
            //hasPermission($victim, $app_name)
            $perms = $SUsersAndGroups->listUserPermissions(PAuthentication::getUserID());
            if(is_array($perms))
            {
                foreach ($perms as $perm)
                {
                    $rigths[strtolower($perm)] = PAuthorisation::PERMIT;
                }
            }
            foreach ($apps as $app)
            {
                if($SUsersAndGroups->hasPermission(PAuthentication::getUserID(), $app))
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
        $uag = SUsersAndGroups::getInstance();
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
            ? SUsersAndGroups::getInstance()->getRealName($this->user)
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
            ? SUsersAndGroups::getInstance()->getEmail($this->user)
            : '';
    }

    public function getRole()
    {
        $SUsersAndGroups = SUsersAndGroups::getInstance();
        return ($SUsersAndGroups->isMemberOf(PAuthentication::getUserID(), 'Administrator'))
            ? (PAuthorisation::ROLE_ADMINISTRATOR)
            : (PAuthorisation::ROLE_USER);
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