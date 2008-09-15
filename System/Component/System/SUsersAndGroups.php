<?php
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 03.09.2008
 * @license GNU General Public License 3
 */
class SUsersAndGroups extends BSystem implements IShareable
{
    private $userlist = null;
    private $grouplist = null;
    private $userlistfile = null;
    private $grouplistfile = null;
    private $systemGroups = array("Administrator", "CMS", "Create",  "Delete", "Edit", "Rename");
        
    public function __construct()
    {
    }

///////////////////////////////////////////////////////////////////////
///// Groups
///////////////////////////////////////////////////////////////////////

////private functions////

    private function checkGroups($groupOrGroups)
    {
        //do they exist?
        $groups = (is_array($groupOrGroups)) ? $groupOrGroups : array($groupOrGroups);
        $checkedGroups = array();
        foreach($groups as $group)
        {
            if($this->isGroup($group))
            {
                $checkedGroups[] = $group;
            }
        }
        return $checkedGroups;
    }
    
    private function saveGroups()
    {
        uksort($this->grouplist, 'strnatcasecmp');
        DFileSystem::SaveData($this->grouplistfile, $this->grouplist);
    }

////public functions////


    public function listSystemGroups()
    {
        return $this->systemGroups;
    }

    public function isSystemGroup($group)
    {
        return in_array($group, $this->systemGroups);
    }

    public function listGroups()
    {
        //list all groups
        return $this->grouplist;
    }
    
    public function addGroup($name, $description = '')
    {
        //add a new group
        $grouplist = &$this->grouplist;
        if(!isset($grouplist[$name]))
        {
            $grouplist[$name] = $description;
            $this->saveGroups();
            return true;
        }
        else
        {
            return false;
        }
    }
        
    public function removeGroup($name)
    {
        //remove a group
        $grouplist = &$this->grouplist;
        $sysgroups = &$this->systemGroups;
        if(isset($grouplist[$name]) && !in_array($name, $sysgroups))
        {
            unset($grouplist[$name]);
            $this->saveGroups();
            return true;
        }
        else
        {
            return false;
        }
    }
        
    public function setGroupDescription($group,$description)
    {
        //set description for a group
        $grouplist = &$this->grouplist;
        $sysgroups = &$this->systemGroups;
        if(isset($grouplist[$group]) && !in_array($group, $sysgroups))
        {
            $grouplist[$group]['description'] = $description;
            $this->saveGroups();
            return true;
        }
        else
        {
            return false;
        }       
    }
        
    public function getGroupDescription($group)
    {
        //set description for a group
        $grouplist = &$this->grouplist;
        if(is_array($grouplist[$group]) && isset($grouplist[$group]['description']))
        {
            return $grouplist[$group]['description'];
        }
        elseif(isset($grouplist[$group]) && !is_array($grouplist[$group]))
        {
            return $grouplist[$group];
        }
        else
        {
            return '';
        }       
    }
    
    public function isGroup($name)
    {
        //does this group exist
        $grouplist = &$this->grouplist;
        return isset($grouplist[$name]);
    }
    
    public function joinGroups($userOrUsers, $groupOrGroups)
    {
        $userlist = &$this->userlist;
        //we want arrays
        $groups = (is_array($groupOrGroups)) ? $groupOrGroups : array($groupOrGroups);
        $users = (is_array($userOrUsers)) ? $userOrUsers : array($userOrUsers);
        //every user joins all groups
        foreach($users as $user)
        {
            if($this->isUser($user))
            {
                $userlist[$user]->joinGroups($this->checkGroups($groups));
            }
        }
        $this->saveUsers();
        return true;
    }
    
    public function setPrimaryGroup($userOrUsers, $group)
    {
        $userlist = &$this->userlist;
        //we want arrays
        $users = (is_array($userOrUsers)) ? $userOrUsers : array($userOrUsers);
        if($this->isGroup($group))
        {
            //every user joins all groups
            foreach($users as $user)
            {
                if($this->isUser($user))
                {
                    $userlist[$user]->setPrimaryGroup($group);
                }
            }
            $this->saveUsers();
            return true;
        }
        return -1;
    }
    
    public function getPrimaryGroup($user)
    {
        $userlist = &$this->userlist;
        if($this->isUser($user))
        {
            return $userlist[$user]->getPrimaryGroup();
        }   
        return -1;
    }
    

    public function leaveGroups($userOrUsers, $groupOrGroups)
    {
        $userlist = &$this->userlist;
        $sysgroups = &$this->systemGroups;
        //we want arrays
        $groups = (is_array($groupOrGroups)) ? $groupOrGroups : array($groupOrGroups);
        $users = (is_array($userOrUsers)) ? $userOrUsers : array($userOrUsers);
        //every user leaves all groups
        foreach($users as $user)
        {
            if($this->isUser($user))
            {
                if(!$userlist[$user]->isMemberOf('Administrator'))
                //this individuum is unimportant, kill it
                {
                    $userlist[$user]->leaveGroups($this->checkGroups($groups));
                }
                else
                //to prevent a deficit of admins: dont let the last quit his job
                {
                    $saveToLeave = array();
                    $unsaveToLeave = array();
                    foreach($groups as $group)
                    {
                        if(!in_array($group, $sysgroups))
                        {
                            $saveToLeave[] = $group;
                        }
                        else
                        {
                            $unsaveToLeave[] = $group;
                        }
                    }
                    $userlist[$user]->leaveGroups($this->checkGroups($saveToLeave));
                    if(count($unsaveToLeave) > 0)
                    {
                        foreach(array_keys($userlist) as $username)
                        {
                            if($username != $user && $userlist[$username]->isMemberOf('Administrator'))
                            {
                                $userlist[$user]->leaveGroups($this->checkGroups($unsaveToLeave));
                            }
                        }
                    }
                }
            }
        }
        $this->saveUsers();
        return true;    
    }
    
    public function listUsersOfGroup($groupname)
    {
        $userlist = &$this->userlist;
        $members = array();
        foreach(array_keys($userlist) as $user)
        {
            if($userlist[$user]->isMemberOf($groupname))
            {
                $members[] = $user;
            }
        }
        return $members;
    }

    public function isMemberOf($user, $group)
    {
        $userlist = &$this->userlist;
        if(isset($userlist[$user]))
        {
            return $userlist[$user]->isMemberOf($group);
        }
        else
        {
            return false;
        }
    }
    
///////////////////////////////////////////////////////////////////////
///// Users
///////////////////////////////////////////////////////////////////////

//PROFILE FUNCTIONS

//wrapper functions: current user and current application
    public function setMyPreference($withKey, $toValue)
    {
        return $this->setUserApplicationPreference(PAuthentication::getUserID(), BAMBUS_APPLICATION, $withKey, $toValue);
    }
    
    public function resetMyPreference($withKeyOrKeys = false)
    {
        //false = reset all keys / string = reset one specific key / array of strings = reset all keys in array
        return $this->resetUserApplicationPreference(PAuthentication::getUserID(), BAMBUS_APPLICATION, $withKeyOrKeys);
    }
    
    public function getMyPreference($withKey)
    {
        return $this->getUserApplicationPreference(PAuthentication::getUserID(), BAMBUS_APPLICATION, $withKey);
    }
    
    
//wrapper functions: current user and specific application
    public function setMyApplicationPreference($ofApplication, $withKey, $toValue)
    {
        return $this->setUserApplicationPreference(PAuthentication::getUserID(), $ofApplication, $withKey, $toValue);
    }
    
    public function resetMyApplicationPreference($ofApplication, $withKeyOrKeys = false)
    {
        //false = reset all keys / string = reset one specific key / array of strings = reset all keys in array
        return $this->resetUserApplicationPreference(PAuthentication::getUserID(), $ofApplication, $withKeyOrKeys);
    }
    
    public function getMyApplicationPreference($ofApplication, $withKey)
    {
        return $this->getUserApplicationPreference(PAuthentication::getUserID(), $ofApplication, $withKey);
    }
    
//specific user and specific application
    public function setUserApplicationPreference($ofUser, $andApplication, $withKey, $toValue, $forcedByAdminPower = false)
    {
        if($this->isUser($ofUser) && ($ofUser == PAuthentication::getUserID() || PAuthorisation::isInGroup('Administrator')))
        {
            //permitted
            $forcedByAdminPower = $forcedByAdminPower && PAuthorisation::isInGroup('Administrator');
            $success = $this->userlist[$ofUser]->setApplicationPreference($andApplication, $withKey, $toValue, $forcedByAdminPower);
            $this->saveUsers();
            return $success;
        }
        else
        {
            //not permitted to change whatever here
            return false;
        }
    }
        
    public function resetUserApplicationPreference($ofUser, $andApplication, $withKeyOrKeys = false)
    {
        //false = reset all keys / string = reset one specific key / array of strings = reset all keys in array
        if($this->isUser($ofUser) && ($ofUser == PAuthentication::getUserID() || PAuthorisation::isInGroup('Administrator')))
        {
            //permitted
            $this->userlist[$ofUser]->resetApplicationPreference($andApplication, $withKeyOrKeys);
            $this->saveUsers();
            return true;
        }
        else
        {
            //not permitted to change whatever here
            return false;
        }
    }
        
    public function getUserApplicationPreference($ofUser, $andApplication, $withKey)
    {
        if($this->isUser($ofUser) && ($ofUser == PAuthentication::getUserID() || PAuthorisation::isInGroup('Administrator')))
        {
            //permitted
            return $this->userlist[$ofUser]->getApplicationPreference($andApplication, $withKey);
        }
        else
        {
            //not permitted to change whatever here
            return false;
        }
    }
    
//END PROFILE   
    
    private function saveUsers()
    {
        uksort($this->userlist, 'strnatcasecmp');
        DFileSystem::SaveData($this->userlistfile, $this->userlist);
    }

    public function listUsers()
    {
        //list all users
        return $this->userlist;
    }
    
    public function listUsersWithRealName()
    {
        $unames = array_keys($this->userlist);
        $rnames = array();
        $ucrnames = array();
        $ret = array();
        for($i = 0; $i < count($unames); $i++)
        {
            $rname = $this->userlist[$unames[$i]]->getRealName();
            $rnames[$i] = (empty($rname)) ? $unames[$i] : $rname;
            $ucrnames[$i] = strtoupper($rnames[$i]);
        }
        asort($ucrnames);
        $keys = array_keys($ucrnames);
        for($i = 0; $i < count($keys); $i++)
            $ret[$unames[$keys[$i]]] = $rnames[$keys[$i]];
        return $ret;
    }
    
    public function getEmail($username)
    {
        $userlist = &$this->userlist;
        if(isset($userlist[$username]))
        {
            return $userlist[$username]->getEmail();
        }
        return false;
    }
    
    public function setUserPassword($username, $password)
    {
        $userlist = &$this->userlist;
        if(isset($userlist[$username]))
        {
            $res = $userlist[$username]->changePassword($password);
            $this->saveUsers();
            return $res;
        }
        return false;
    }
    
    public function setUserEmail($username, $email)
    {
        $userlist = &$this->userlist;
        if(isset($userlist[$username]))
        {
            $res = $userlist[$username]->setEmail($email);
            $this->saveUsers();
            return $res;
        }
        return false;
    }
    
    public function getRealName($username)
    {
        $userlist = &$this->userlist;
        if(isset($userlist[$username]))
        {
            return $userlist[$username]->getRealName();
        }
        return false;   
    }
    
    public function setUserRealName($username, $realName)
    {
        $userlist = &$this->userlist;
        if(isset($userlist[$username]))
        {
            $res = $userlist[$username]->setRealName($realName);
            $this->saveUsers();
            return $res;
        }
        return false;
    }
    
    
    public function getPasswordHash($username)
    {
        $userlist = &$this->userlist;
        if(isset($userlist[$username]))
        {
            return $userlist[$username]->getPasswordHash();
        }
        return false;   
    }
    
    public function addUser($name, $password,  $realName = "", $email = "", $groups  = "", $permissions = "")
    {
        //add a new user
        $userlist = &$this->userlist;
        if(!isset($userlist[$name]))
        {
            //let the sublass do the work
            $userlist[$name] = new SUser($password,  $realName, $email, $this->checkGroups($groups), $permissions);
            $this->saveUsers();
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function removeUser($name)
    {
        //check for admistatus etc and remove only if there is a redundant use of admins
        $userlist = &$this->userlist;
        if(isset($userlist[$name]))
        {
            if(!$userlist[$name]->isMemberOf('Administrator'))
            //this individuum is unimportant, kill it
            {
                unset($userlist[$name]);
                $this->saveUsers();
                return true;
            }
            else
            //to prevent a deficit of admins: dont delete the last
            {
                foreach(array_keys($userlist) as $username)
                {
                    if($username != $name && $userlist[$username]->isMemberOf('Administrator'))
                    {
                        unset($userlist[$name]);
                        $this->saveUsers();
                        return true;
                    }
                }
                return -1;
            }
        }
        else
        {
            return false;
        }
    }
    
    public function isUser($name)
    {
        $userlist = &$this->userlist;
        return isset($userlist[$name]);
    }
    
    public function isValidUser($name, $password)
    {
        $userlist = &$this->userlist;
        if(isset($userlist[$name]))
        {
            return $userlist[$name]->validatePassword($password);
        }
        return false; 
    }
    
    public function listGroupsOfUser($username, $listSystemGroups = true)
    {
        $userlist = &$this->userlist;
        if($this->isUser($username))
        {
            //return $userlist[$username]->listGroups();
            $groups = $userlist[$username]->listGroups();
            $retval = array();
            foreach($groups as $group)
            {
                if($this->isGroup($group) && $listSystemGroups)
                {
                    $retval[] = $group;
                }
                if(!$this->isSystemGroup($group) && $this->isGroup($group) && !$listSystemGroups)
                {
                    $retval[] = $group;
                }
            }
            return $retval;
        }
        else
        {
            return false;
        }
    }
    
    public function getUserAttribute($username, $attributename){
        $userlist = &$this->userlist;
        if($this->isUser($username))
        {
            return $userlist[$username]->getAttribute($attributename);
        }
        else
        {
            return false;
        }
    }
    
    public function listUserAttributes($username){
        $userlist = &$this->userlist;
        if($this->isUser($username))
        {
            return $userlist[$username]->listAttributes();
        }
        else
        {
            return false;
        }   
    }
    
    public function setUserAttribute($username, $attiributename, $value){
        $userlist = &$this->userlist;
        if($this->isUser($username))
        {
            $userlist[$username]->setAttribute($attiributename, $value);
            $this->saveUsers();
            return true;
        }
        else
        {
            return false;
        }       
    }
    
    public function grantUserPermissions($userOrUsers, $permissionOrPermissions)
    {
        $userlist = &$this->userlist;
        $users = (is_array($userOrUsers)) ? $userOrUsers : array($userOrUsers);
        foreach($users as $user)
        {
            if($this->isUser($user))
            {
                $userlist[$user]->grantPermissions($permissionOrPermissions);
            }
        }
        $this->saveUsers();
        return true;
    }
    
    public function listUserPermissions($user){
        $userlist = &$this->userlist;
        if($this->isUser($user))
        {
            return $userlist[$user]->listPermissions();
        }
        else
        {
            return false;
        }
    }
    
    public function calaculatePermissions($permissions = array())
    {
        //0 > disable_by_default
        //1 > force_disabled
        //2 > enable_by_default
        //3 > force_enabled
        if(!is_array($permissions)) $permissions = array($permissions);
        if(in_array(1, $permissions)) return 1; //forced disable has the highest priority
        if(in_array(3, $permissions)) return 3; //forced enable
        if(in_array(0, $permissions)) return 0; //disable has a higher priority than enable
        if(in_array(2, $permissions)) return 2; //everybody say enable
        return 0;//nothing given -> disable
    }
    
    public function rejectUserPermissions($userOrUsers, $permissionOrPermissions)
    {
        $userlist = &$this->userlist;
        $users = (is_array($userOrUsers)) ? $userOrUsers : array($userOrUsers);
        foreach($users as $user)
        {
            if($this->isUser($user))
            {
                $userlist[$user]->rejectPermissions($permissionOrPermissions);
            }
        }
        $this->saveUsers();
        return true;
    }
    
    public function hasPermission($user, $permission)
    {
        $userlist = &$this->userlist;
        if($this->isUser($user))
        {
            $permissions = $userlist[$user]->listPermissions();
            return in_array($permission, $permissions);
        }
        return false;
    }
    
    
    
    
    //IShareable
    const CLASS_NAME = 'SUsersAndGroups';
    public static $sharedInstance = NULL;

    /**
     * @return SUsersAndGroups
     */
    public static function alloc()
    {
        $class = self::CLASS_NAME;
        if(self::$sharedInstance == NULL && $class != NULL)
        {
            self::$sharedInstance = new $class();
        }
        return self::$sharedInstance;
    }
    
    /**
     * @return SUsersAndGroups
     */
    function init()
    {
        if($this->userlistfile == null)
        {
            //FIXME Users hard linked
            $this->userlistfile = SPath::CONTENT.'configuration/users.php';
            $this->userlist = DFileSystem::LoadData($this->userlistfile);
            //FIXME Groups hard linked
            $this->grouplistfile = SPath::CONTENT.'configuration/groups.php';
            $this->grouplist = DFileSystem::LoadData($this->grouplistfile);
        }
        return $this;
    }
    //end IShareable
}
?>