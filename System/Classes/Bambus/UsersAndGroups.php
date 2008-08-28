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
class UsersAndGroups extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'UsersAndGroups';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
			$this->FileSystem = FileSystem::alloc();

			$this->FileSystem->init();
    	}
    }
	//end IShareable

	public $foo = 'bar';
	function allowCallFromTemplate(){return true;}

    var $userlist,$grouplist;
    var $userlistfile,$grouplistfile;
    var $systemGroups;
        
    function __construct()
    {
        parent::Bambus();
        $phpVersionArray = explode('.', phpversion());
		$phpVersion = $phpVersionArray[0];
		$path = substr(__FILE__,0,-4).'/';
		if(file_exists($path.'bcmsuser.php'.$phpVersion.'.php'))
		{
			//is there a version of the class optimized for this php-version
			require_once($path.'bcmsuser.php'.$phpVersion.'.php');
		}
		else
		{
			require_once($path.'bcmsuser.php');
		}
    }
    
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
	function loadVars($nil1, $nil2, $nil3, $nil4)
	{
		//unchangeable groups owned by bambus
		$this->systemGroups = array("Administrator", "PHP", "CMS", "Create",  "Delete", "Edit", "Rename");
		//load the config files for:
        //Users
        $this->userlistfile = parent::pathToFile('userList');
		$this->userlist = $this->readData($this->userlistfile);
		//Groups
        $this->grouplistfile = parent::pathToFile('groupList');
		$this->grouplist = $this->readData($this->grouplistfile);
	}
	
   function readData($file)
    {
    	$ret = array();
        if(file_exists($file) && is_readable($file))
        {
            $data = file($file);
            if(count($data) >= 2)
            {
                unset($data[0]);
                $dataString = implode('',$data);
                $data = @unserialize($dataString);
                if(!empty($data))
                {
                    $ret = $data;
                }
            }
        }
        return $ret;
    }    
    

	function ucsort(&$array)
	{
		$keys = array_keys($array);
		$sortHelper = array();
		foreach($keys as $key)
		{
			$sortHelper[$key] = strtoupper($key);
		}
		asort($sortHelper);
		$keys = array_keys($sortHelper);
		$sortHelper = array();
		foreach($keys as $key)
		{
			$sortHelper[$key] = $array[$key];
		}
		$array = $sortHelper;
	}

///////////////////////////////////////////////////////////////////////
///// Groups
///////////////////////////////////////////////////////////////////////

////private functions////

	function checkGroups($groupOrGroups)
	{
		//do they exist?
		$groups = $this->isAnArray($groupOrGroups);
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
	
	function saveGroups(){
		//$data = $this->bgfstring."\n".serialize($this->grouplist);
		$this->ucsort($this->grouplist);
		$this->FileSystem->writeData($this->grouplistfile, $this->grouplist);
	}

////public functions////


	function listSystemGroups()
	{
		return $this->systemGroups;
	}

	function isSystemGroup($group)
	{
		return in_array($group, $this->systemGroups);
	}

	function listGroups()
	{
		//list all groups
		return $this->grouplist;
	}
	
	function addGroup($name, $description = '')
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
		
	function removeGroup($name)
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
		
	function setGroupDescription($group,$description)
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
		
	function getGroupDescription($group)
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
	
	function isGroup($name)
	{
		//does this group exist
		$grouplist = &$this->grouplist;
		return isset($grouplist[$name]);
	}
	
	function joinGroups($userOrUsers, $groupOrGroups)
	{
		$userlist = &$this->userlist;
		//we want arrays
		$groups = $this->isAnArray($groupOrGroups);
		$users = $this->isAnArray($userOrUsers);
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
	
	function getGroupPermissions($group){
		$grouplist = &$this->grouplist;
		if($this->isGroup($group))
		{
			if(isset($grouplist[$group]['permissions']) && is_array($grouplist[$group]['permissions']))
			{
				return $grouplist[$group]['permissions'];
			}
			else
			{
				return array();
			}
		}
		return -1;
	}
	
	function setPrimaryGroup($userOrUsers, $group)
	{
		$userlist = &$this->userlist;
		//we want arrays
		$users = $this->isAnArray($userOrUsers);
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
	
	function getPrimaryGroup($user)
	{
		$userlist = &$this->userlist;
		if($this->isUser($user))
		{
			return $userlist[$user]->getPrimaryGroup();
		}	
		return -1;
	}
	
	function getGroupPermission($group, $editor)
	{
		$grouplist = &$this->grouplist;
		if($this->isGroup($group))
		{
			if(isset($grouplist[$group]['permissions']) && is_array($grouplist[$group]['permissions']))
			{
				//this group has permission settings
				if(isset($grouplist[$group]['permissions'][$editor]))
				{
					return $grouplist[$group]['permissions'][$editor];
				}
				
			}
			return 0;
		}
		return -1;
	}
	
	function setGroupPermission($group, $editor, $modus)
	{
		$grouplist = &$this->grouplist;
		if($this->isGroup($group))
		{
			//is it an array? 
			if(!is_array($grouplist[$group]))
			{
				$grouplist[$group] = array('description' => $grouplist[$group]);
			}
			//got permissions list?
			if(!isset($grouplist[$group]['permissions']))
			{
				$grouplist[$group]['permissions'] = array();
			}
			//we only want NUMB3RS
			if(is_numeric($modus))
			{
				$grouplist[$group]['permissions'][$editor] = $modus;
				return true;
			}
		}
		return false;
	}

	function leaveGroups($userOrUsers, $groupOrGroups)
	{
		$userlist = &$this->userlist;
		$sysgroups = &$this->systemGroups;
		//we want arrays
		$groups = $this->isAnArray($groupOrGroups);
		$users = $this->isAnArray($userOrUsers);
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
	
	function listUsersOfGroup($groupname)
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

	function isMemberOf($user, $group)
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
	function setMyPreference($withKey, $toValue)
	{
		return $this->setUserApplicationPreference(BAMBUS_USER, BAMBUS_APPLICATION, $withKey, $toValue);
	}
	
	function resetMyPreference($withKeyOrKeys = false)
	{
		//false = reset all keys / string = reset one specific key / array of strings = reset all keys in array
		return $this->resetUserApplicationPreference(BAMBUS_USER, BAMBUS_APPLICATION, $withKeyOrKeys);
	}
	
	function getMyPreference($withKey)
	{
		return $this->getUserApplicationPreference(BAMBUS_USER, BAMBUS_APPLICATION, $withKey);
	}
	
	function isMyPreferenceForced($withKey)
	{
		return $this->isUserApplicationPreferenceForced(BAMBUS_USER, BAMBUS_APPLICATION, $withKey);
	}
	
//wrapper functions: current user and specific application
	function setMyApplicationPreference($ofApplication, $withKey, $toValue)
	{
		return $this->setUserApplicationPreference(BAMBUS_USER, $ofApplication, $withKey, $toValue);
	}
	
	function resetMyApplicationPreference($ofApplication, $withKeyOrKeys = false)
	{
		//false = reset all keys / string = reset one specific key / array of strings = reset all keys in array
		return $this->resetUserApplicationPreference(BAMBUS_USER, $ofApplication, $withKeyOrKeys);
	}
	
	function getMyApplicationPreference($ofApplication, $withKey)
	{
		return $this->getUserApplicationPreference(BAMBUS_USER, $ofApplication, $withKey);
	}
	
	function isMyApplicationPreferenceForced($ofApplication, $withKey)
	{
		return $this->isUserApplicationPreferenceForced(BAMBUS_USER, $ofApplication, $withKey);
	}
	
//specific user and specific application
	function setUserApplicationPreference($ofUser, $andApplication, $withKey, $toValue, $forcedByAdminPower = false)
	{
		if($this->isUser($ofUser) && ($ofUser == BAMBUS_USER || BAMBUS_GRP_ADMINISTRATOR))
		{
			//permitted
			$forcedByAdminPower = $forcedByAdminPower && BAMBUS_GRP_ADMINISTRATOR;
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
	
	function setUserApplicationPreferenceForce($ofUser, $andApplication, $yesOrNo)
	{
		if(BAMBUS_GRP_ADMINISTRATOR)
		{
			//permitted
			$success = $this->userlist[$ofUser]->setApplicationPreferenceForce($andApplication, $yesOrNo);
			$this->saveUsers();
			return $success;
		}
		else
		{
			//not permitted to change whatever here
			return false;
		}
	}
		
	function getUserApplicationPreferenceForce($ofUser, $andApplication)
	{
		if(BAMBUS_GRP_ADMINISTRATOR)
		{
			//permitted
			return $this->userlist[$ofUser]->getApplicationPreferenceForce($andApplication);
		}
		else
		{
			//not permitted to change whatever here
			return false;
		}
	}
		
	function setUserPreferenceForce($ofUser, $yesOrNo)
	{
		if(BAMBUS_GRP_ADMINISTRATOR)
		{
			//permitted
			$success = $this->userlist[$ofUser]->setPreferenceForce($yesOrNo);
			$this->saveUsers();
			return $success;
		}
		else
		{
			//not permitted to change whatever here
			return false;
		}
	}
		
	function getUserPreferenceForce($ofUser)
	{
		if(BAMBUS_GRP_ADMINISTRATOR)
		{
			//permitted
			return $this->userlist[$ofUser]->getPreferenceForce();
		}
		else
		{
			//not permitted to change whatever here
			return false;
		}
	}
		
	function resetUserApplicationPreference($ofUser, $andApplication, $withKeyOrKeys = false)
	{
		//false = reset all keys / string = reset one specific key / array of strings = reset all keys in array
		if($this->isUser($ofUser) && ($ofUser == BAMBUS_USER || BAMBUS_GRP_ADMINISTRATOR))
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
	function isUserApplicationPreferenceForced($ofUser, $andApplication, $withKey)
	{
		if($this->isUser($ofUser) && ($ofUser == BAMBUS_USER || BAMBUS_GRP_ADMINISTRATOR))
		{
			return $this->userlist[$ofUser]->isApplicationPreferenceForced($andApplication, $withKey);
		}		
	}
		
	function getUserApplicationPreference($ofUser, $andApplication, $withKey)
	{
		if($this->isUser($ofUser) && ($ofUser == BAMBUS_USER || BAMBUS_GRP_ADMINISTRATOR))
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
	
	function listUsersHavingKeySetToThis($key, $valueToMatch)
	{
		return $this->listUsersHavingApplicationKeySetToThis(BAMBUS_APPLICATION, $key, $valueToMatch);
	}
	
	function listUsersHavingApplicationKeySetToThis($application, $key, $valueToMatch)
	{
		$users = array_keys($this->userlist);
		$found = array();
		foreach($users as $user)
		{
			if($this->userlist[$user]->getApplicationPreference($application, $key) == $valueToMatch)
			{
				$found[] = $user;
			}
		}
		return $found;
	}
	
//END PROFILE	
////private functions////
	
	function saveUsers()
	{
		//write to userfile
		//$data = $this->bufstring."\n".serialize($this->userlist);
		$this->ucsort($this->userlist);
		$this->FileSystem->writeData($this->userlistfile, $this->userlist);
	}

////public functions////

	function listUsers()
	{
		//list all users
		return $this->userlist;
	}
	
	function listUsersWithRealName()
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
	
	function getEmail($username)
	{
		$userlist = &$this->userlist;
		if(isset($userlist[$username]))
		{
			return $userlist[$username]->getEmail();
		}
		return false;
	}
	
	function setUserPassword($username, $password)
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
	
	function setUserEmail($username, $email)
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
	
	function getRealName($username)
	{
		$userlist = &$this->userlist;
		if(isset($userlist[$username]))
		{
			return $userlist[$username]->getRealName();
		}
		return false;	
	}
	
	function setUserRealName($username, $realName)
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
	
	
	function getPasswordHash($username)
	{
		$userlist = &$this->userlist;
		if(isset($userlist[$username]))
		{
			return $userlist[$username]->getPasswordHash();
		}
		return false;	
	}
	
	function addUser($name, $password,  $realName = "", $email = "", $groups  = "", $permissions = "")
	{
		//add a new user
		$userlist = &$this->userlist;
		if(!isset($userlist[$name]))
		{
			//let the sublass do the work
			$userlist[$name] = new bcmsuser($password,  $realName, $email, $this->checkGroups($groups), $permissions);
			$this->saveUsers();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function removeUser($name)
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
	
	function isUser($name)
	{
		$userlist = &$this->userlist;
		return isset($userlist[$name]);
	}
	
	function isValidUser($name, $password)
	{
		$userlist = &$this->userlist;
		if(isset($userlist[$name]))
		{
			return $userlist[$name]->validatePassword($password);
		}
		return false; 
	}
	
	function listGroupsOfUser($username, $listSystemGroups = true)
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
	
	function getUserAttribute($username, $attributename){
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
	
	function listUserAttributes($username){
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
	
	function setUserAttribute($username, $attiributename, $value){
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
	
	function grantUserPermissions($userOrUsers, $permissionOrPermissions)
	{
		$userlist = &$this->userlist;
		$users = $this->isAnArray($userOrUsers);
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
	
	function listUserPermissions($user){
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
	
	function calaculatePermissions($permissions = array())
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
	
	function rejectUserPermissions($userOrUsers, $permissionOrPermissions)
	{
		$userlist = &$this->userlist;
		$users = $this->isAnArray($userOrUsers);
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
	
	function hasPermission($user, $permission)
	{
		$userlist = &$this->userlist;
		if($this->isUser($user))
		{
			$permissions = $userlist[$user]->listPermissions();
			return in_array($permission, $permissions);
		}
		return false;
	}
}
?>