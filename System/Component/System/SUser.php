<?php
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 03.09.2008
 * @license GNU General Public License 3
 */
class SUser 
    extends 
        BSystem 
{
    public $password,$realName,$email;
    public $groups = array();
    public $permissions = array();
    public $attributes = array();
    public $primaryGroup = '';
    
    public $applicationPreferences = array();
    public $applicationPreferenceKeyForces = array();
    public $applicationPreferenceForces = array();
    public $preferenceForced = false;
    
    public function __construct($password, $realName = "", $email = "", $groups = "", $permissions = "", $primaryGroup = "")
    {
        $this->changePassword($password);
        $this->setRealName($realName);
        $this->setEmail($email);
        $this->joinGroups($groups);
        $this->grantPermissions($permissions);
        $this->setPrimaryGroup($primaryGroup);
    }
    
    public function __sleep()
    {
        return array_keys(get_class_vars('SUser'));
        //return array('password', 'realName', 'email', 'groups', 'permissions', 'attributes', 'applicationPreferences', 'applicationPreferenceKeyForces', 'applicationPreferenceForces', 'preferenceForced');
    }
    
////Preferences
    public function setApplicationPreference($application, $key, $value, $withAdminPower = false)
    {
        //init empty fields
        if(!isset($this->applicationPreferences))
        {
            $this->applicationPreferences = array();
        }
        if(!isset($this->applicationPreferences[$application]))
        {
            $this->applicationPreferences[$application] = array();
        }
        
        //only set if permitted
        if(
            PAuthorisation::isInGroup('Administrator')
            ||
            (
                !$this->preferenceForced 
                && 
                empty($this->applicationPreferenceForces[$application])
                &&
                empty($this->applicationPreferenceKeyForces[$application][$key])
            )
          )
        {
            //set value
            $this->applicationPreferences[$application][$key] = $value;
            //set permission for this key
            $this->applicationPreferenceKeyForces[$application][$key] = $withAdminPower;
        }
    }
    
    public function resetApplicationPreference($application, $keyOrKeysOrNothing = false)
    {
        if((isset($this->applicationPreferences)) && (isset($this->applicationPreferences[$application])))
        {
            //does not make sense to reset nothing
            if($keyOrKeysOrNothing === false)
            {
                //reset all
                $keyOrKeysOrNothing = array_keys($this->applicationPreferences[$application]);
            }
            elseif(!is_array($keyOrKeysOrNothing))
            {
                $keyOrKeysOrNothing = array($keyOrKeysOrNothing);
            }
            //null every key we got
            foreach($keyOrKeysOrNothing as$key)
            {
                if(isset($this->applicationPreferences[$application][$key]))
                {
                    unset($this->applicationPreferences[$application][$key]);
                }
            }
        }
    }
    
    public function getApplicationPreference($application, $key)
    {
        if(isset($this->applicationPreferences[$application][$key]))
        {
            return $this->applicationPreferences[$application][$key];
        }
        else
        {
            return '';
        }
    }
    
    public function listApplicationPreferenceKeys($application)
    {
        if(!isset($this->applicationPreferences[$application]))
        {
            return array_keys($this->applicationPreferences[$application]);
        }
        else
        {
            return array();
        }
        
    }
        
    public function listApplicationsWithPreferences()
    {
        if(!isset($this->applicationPreferences))
        {
            return array_keys($this->applicationPreferences);
        }
        else
        {
            return array();
        }
        
    }
        
    public function listApplicationPreferences($application)
    {
        if(!isset($this->applicationPreferences[$application]))
        {
            return $this->applicationPreferences[$application];
        }
        else
        {
            return array();
        }
        
    }
    
////Permissions
    public function grantPermissions($permissionOrPermissions)
    //grant application permissions
    {
        $userPermissions = &$this->permissions;
        $permissions = (is_array($permissionOrPermissions)) ? $permissionOrPermissions : array($permissionOrPermissions);
        foreach($permissions as $permission)
        {
            if(!in_array($permission, $userPermissions) && !empty($permission))
            {
                $userPermissions[] = $permission;
            }
        }
        return true;
    }
    
    public function rejectPermissions($permissionOrPermissions)
    //reject application permissions
    {       
        $userPermissions = &$this->permissions;
        $permissionsToReject = (is_array($permissionOrPermissions)) ? $permissionOrPermissions : array($permissionOrPermissions);
        $newPermissions = array();
        foreach($userPermissions as $permission)
        {
            if(!in_array($permission, $permissionsToReject))
            {
                $newPermissions[] = $permission;
            }
        }
        $userPermissions = $newPermissions;
    }
    
    public function listPermissions()
    //get a list of all allowed editors
    {
        return $this->permissions;
    }
////Attributes
    public function getAttribute($key)
    {
        $attributes = &$this->attributes;
        if(isset($attributes[$key]))
        {
            return $attributes[$key];
        }
        else
        {
            return '';
        }
    }
    
    public function setAttribute($key, $value)
    {
        $attributes = &$this->attributes;
        $attributes[$key] = $value;
        return true;
    }
    
    public function listAttributes()
    {
        return $this->attributes;
    }
////Groups  
    public function setPrimaryGroup($group)
    {
        if(in_array($group, $this->groups))
        {
            $this->primaryGroup = $group;
            return true;
        }
        return false;
    }
    
    public function getPrimaryGroup()
    {
        return $this->primaryGroup;
    }
        
    public function joinGroups($groupOrGroups)
    {
        $userGroups = &$this->groups;
        $groups = (is_array($groupOrGroups)) ? $groupOrGroups : array($groupOrGroups);
        foreach($groups as $group)
        {
            if(!in_array($group, $userGroups) && !empty($group))
            {
                $userGroups[] = $group;
            }
        }
    }
    
    public function leaveGroups($groupOrGroups)
    {
        $userGroups = &$this->groups;
        $groupsToBeLeft = (is_array($groupOrGroups)) ? $groupOrGroups : array($groupOrGroups);
        $newUserList = array();
        foreach($userGroups as $group)
        {
            if(!in_array($group, $groupsToBeLeft))
            {
                $newUserList[] = $group;
            }
        }
        $userGroups = $newUserList;
    }   
    
    public function listGroups()
    {
        return $this->groups;
    }
    
    public function isMemberOf($group)
    {
        $groups = $this->groups;
        return in_array($group, $groups);
    }
////userinfos   
    public function changePassword($newPassword)
    {
        $this->password = md5($newPassword);
    }
    
    public function getPasswordHash()
    {
        return $this->password;
    }
    
    public function validatePassword($password)
    {
        return $this->password == md5($password);
    }
    
    public function setRealName($realName)
    {
        $this->realName = $realName;
    }
    
    public function getRealName()
    {
        return $this->realName;
    }
    
    public function setEmail($email)
    {
        if(
            (strlen($email) >= 6) &&
            (strpos($email,'@') !== false) &&
            (strpos($email,'.') !== false)
          )
        {
            $this->email = $email;
        }
        else
        {
            $this->email = false;
        }
    }
    
    public function getEmail()
    {
        return $this->email;
    }
}
?>