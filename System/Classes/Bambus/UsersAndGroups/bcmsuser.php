<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 04.09.2006
 * @license GNU General Public License 3
 */
class bcmsuser{
	var $password,$realName,$email;
	var $groups = array();
	var $permissions = array();
	var $attributes = array();
	var $primaryGroup = '';
	
	var $applicationPreferences = array();
	var $applicationPreferenceKeyForces = array();
	var $applicationPreferenceForces = array();
	var $preferenceForced = false;
	
 	function bcmsuser($password, $realName = "", $email = "", $groups = "", $permissions = "", $primaryGroup = "")
 	{
 		$this->changePassword($password);
 		$this->setRealName($realName);
 		$this->setEmail($email);
		$this->joinGroups($groups);
		$this->grantPermissions($permissions);
		$this->setPrimaryGroup($primaryGroup);
 	}
////Preferences
	function setApplicationPreference($application, $key, $value, $withAdminPower = false)
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
			BAMBUS_GRP_ADMINISTRATOR
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
	
	function resetApplicationPreference($application, $keyOrKeysOrNothing = false)
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
				//TODO: check permissions
				if(isset($this->applicationPreferences[$application][$key]))
					unset($this->applicationPreferences[$application][$key]);
			}
		}
	}
	
	function getApplicationPreference($application, $key)
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
	
	function setPreferenceForce($yesOrNo)
	{
		if(BAMBUS_GRP_ADMINISTRATOR)
			$this->preferenceForced = $yesOrNo;
	}
	
	function getPreferenceForce()
	{
		return $this->preferenceForced;
	}
	
	function setApplicationPreferenceForce($application, $yesOrNo)
	{
		if(BAMBUS_GRP_ADMINISTRATOR)
			$this->applicationPreferenceForces[$application] = $yesOrNo;
	}
	
	function getApplicationPreferenceForce($application)
	{
		return !empty($this->applicationPreferenceForces[$application]);
	}
	
	function listApplicationPreferenceKeys($application)
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
		
	function listApplicationsWithPreferences()
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
		
	function listApplicationPreferences($application)
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
	
	function isApplicationPreferenceForced($application = false, $key = false)
	{
		$keyForce = ($key) ? false : !empty($this->applicationPreferenceKeyForces[$application][$key]);
		$appForce = ($application) ? false : !empty($this->applicationPreferenceForces[$application]);
		$generalForce = $this->preferenceForced;
		return ($generalForce || $appForce || $keyForce);
	}
	
////Permissions
	function grantPermissions($permissionOrPermissions)
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
	
	function rejectPermissions($permissionOrPermissions)
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
	
	function listPermissions()
	//get a list of all allowed editors
	{
		return $this->permissions;
	}
////Attributes
	function getAttribute($key)
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
	
	function setAttribute($key, $value)
	{
		$attributes = &$this->attributes;
		$attributes[$key] = $value;
		return true;
	}
	
	function listAttributes()
	{
		return $this->attributes;
	}
////Groups 	
	function setPrimaryGroup($group)
	{
		if(in_array($group, $this->groups))
		{
			$this->primaryGroup = $group;
		}
	}
	
	function getPrimaryGroup()
	{
		return $this->primaryGroup;
	}
		
 	function joinGroups($groupOrGroups)
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
 	
 	function leaveGroups($groupOrGroups)
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
 	
 	function listGroups()
 	{
 		return $this->groups;
 	}
 	
 	function isMemberOf($group)
 	{
 		$groups = $this->groups;
 		return in_array($group, $groups);
 	}
////userinfos 	
 	function changePassword($newPassword)
 	{
 		$this->password = md5($newPassword);
 	}
 	
 	function getPasswordHash()
 	{
 		return $this->password;
 	}
 	
 	function validatePassword($password)
 	{
 		return $this->password == md5($password);
 	}
 	
 	function setRealName($realName)
 	{
 		$this->realName = $realName;
 	}
 	
 	function getRealName()
 	{
 		return $this->realName;
 	}
 	
 	function setEmail($email)
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
	function getEmail()
	{
		return $this->email;
	}
}
?>