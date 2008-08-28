<?php
/************************************************
* Bambus CMS 
* Created:     03. Nov 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
if(!empty($post['ta_size'])){
    setcookie('bambus_ta_size', $post['ta_size']);
}
$dbNeedsUpdate = false;
////////////////////
//what do we edit?//
////////////////////

$edit = (!isset($get['edit'])) ? '' : $get['edit'];
if(empty($get['mode']) || $get['mode'] == 'usr')
{
	/////////
	//users//
	/////////
	
	$edit_mode = 'usr';
	$victim = (($Bambus->UsersAndGroups->isUser($edit)) ? $edit : BAMBUS_USER);
}
else
{	
	//////////
	//groups//
	//////////
	
	if($Bambus->UsersAndGroups->isGroup($edit))
	{
		$victim = $edit;
		$edit_mode = 'grp';
	}
	else
	{
		//no group found?
		$victim = BAMBUS_USER;
		$edit_mode = 'usr';
	}
}

//////////////////
//process inputs//
//////////////////

if(!empty($post['action']))
{
	/////////////////////
	//edit user profile//
	/////////////////////
	
	if($post['action'] == 'edit_user_data' && BAMBUS_GRP_EDIT && ($victim == BAMBUS_USER || BAMBUS_GRP_ADMINISTRATOR))
	{
		$Bambus->UsersAndGroups->setUserRealName($victim, $post['realName']);
		$Bambus->UsersAndGroups->setUserEmail($victim, $post['email']);
		$Bambus->UsersAndGroups->setUserAttribute($victim, 'company', $post['att_company']);
		SNotificationCenter::alloc()->init()->report('message', 'user_profile_saved');
		if(BAMBUS_GRP_ADMINISTRATOR)
		{
			if(!empty($post['adm_set_password']) && !empty($post['adm_set_password_confirm']) &&$post['adm_set_password_confirm'] == $post['adm_set_password'])
			{
				$Bambus->UsersAndGroups->setUserPassword($victim, $post['adm_set_password']);
			}
			if(!empty($post['adm_set_password_confirm']) &&!empty($post['adm_set_password']) && md5($post['adm_set_password_confirm']) == $Bambus->UsersAndGroups->getPasswordHash($victim))
			{
				SNotificationCenter::alloc()->init()->report('message', 'new_password_set');
			}
			elseif(!empty($post['adm_set_password']))
			{
				SNotificationCenter::alloc()->init()->report('alert', 'passwords_do_not_match');
			}
		}
		else
		{
			if(
				(!empty($post['change_password_from_old']) && $Bambus->UsersAndGroups->isValidUser($victim, $post['change_password_from_old']))
				&&
				(!empty($post['change_password_to_new']) && !empty($post['change_password_confirm']) && $post['change_password_to_new'] == $post['change_password_confirm'])
			  )
			{
				$Bambus->UsersAndGroups->setUserPassword($victim, $post['change_password_to_new']);
				SNotificationCenter::alloc()->init()->report('message', 'password_changed');
			}
			elseif(!empty($post['change_password_from_old']) && $Bambus->UsersAndGroups->isValidUser($victim, $post['change_password_from_old']))
			{
				SNotificationCenter::alloc()->init()->report('alert', 'passwords_do_not_match');
			}
		}
	}

	/////////////////
	//group actions//
	/////////////////
	
	if($post['action'] == 'create_new_group' && BAMBUS_GRP_CREATE)
	{
		if(!empty($post['new_group_name']) && isset($post['new_group_description']))
		{
			$Bambus->UsersAndGroups->addGroup($post['new_group_name'], $post['new_group_description']);
			SNotificationCenter::alloc()->init()->report('message', 'group_created');
			$edit = $post['new_group_name'];
			$victim = $post['new_group_name'];
			$edit_mode = 'grp';
		}
		else
		{
			SNotificationCenter::alloc()->init()->report('warning', 'no_group_name_specified');
		}
	}
	////////////////
	//user actions//
	////////////////
	
	if($post['action'] == 'create_new_user' && BAMBUS_GRP_CREATE)
	{
		if(
		  	(!empty($post['new_user_name'])) &&
		  	(!empty($post['new_user_password'])) &&
		  	(!empty($post['new_user_password_check'])) &&
		  	(!$Bambus->UsersAndGroups->isUser($post['new_user_name'])) &&
		  	($post['new_user_password'] == $post['new_user_password_check'])
		  )
		{
			///////////////
			//create user//
			///////////////
			
			$Bambus->UsersAndGroups->addUser($post['new_user_name'], $post['new_user_password'], $post['new_user_name_and_surname'], $post['new_user_email']);
			SNotificationCenter::alloc()->init()->report('message', 'user_created');
			$victim = $post['new_user_name'];
			$edit_mode = 'usr';
			$Bambus->Linker->set('get', 'edit', $victim);
			$Bambus->Linker->set('get', 'mode', 'usr');
		}
		else
		{
			/////////////////////////
			//display error message//
			/////////////////////////
			
			if(($post['new_user_password']) != ($post['new_user_password_check']))
			{
				SNotificationCenter::alloc()->init()->report('warning', 'passwords_not_equal');
			}
			elseif($Bambus->UsersAndGroups->isUser($post['new_user_name']))
			{
				SNotificationCenter::alloc()->init()->report('warning', 'user_already_exists');
			}
			elseif(empty($post['new_user_name']))
			{
				SNotificationCenter::alloc()->init()->report('warning', 'username_has_to_be_set');
			}
			elseif(empty($post['new_user_password']))
			{
				SNotificationCenter::alloc()->init()->report('warning', 'password_has_to_be_set');
			}
			else
			{
				SNotificationCenter::alloc()->init()->report('warning', 'failed_to_create_user');
			}
		}
	}
	
	/////////////////////////
	//save group assignment//
	/////////////////////////
	
	if($post['action'] == 'save_assignment_of_groups' && BAMBUS_GRP_EDIT)
	{
		$join = array();
		$leave = array();
		//you cant change your own system rights
		if($victim != BAMBUS_USER)
		{
			////////////////////////////////
			//change system group settings//
			////////////////////////////////
			
			if(empty($post['join_group_Administrator']))
			{
				//no administrator 
				foreach($Bambus->UsersAndGroups->listSystemGroups() as $systemGroup)
				{
					if(!empty($post['join_group_'.md5($systemGroup)]))
					{
						$join[$systemGroup] = $systemGroup;
					}
					else
					{
						$leave[$systemGroup] = $systemGroup;
					}
				}
			}
			else
			{
				//platin member: access to everything 
				foreach($Bambus->UsersAndGroups->listSystemGroups() as $systemGroup)
				{
					$join[$systemGroup] = $systemGroup;
				}			
			}
		}
		else
		{
			$groups = $Bambus->UsersAndGroups->listGroupsOfUser(BAMBUS_USER);
			foreach($groups as $group)
			{	
				if($Bambus->UsersAndGroups->isSystemGroup($group))
					$join[$group] = $group;
			}
		}
		
		////////////////////////////////
		//change custom group settings//
		////////////////////////////////
		
		foreach(array_keys($Bambus->UsersAndGroups->listGroups()) as $group)
		{
			if(!empty($post['join_group_'.md5($group)]))
			{
				$join[$group] = $group;
			}
			else
			{
				$leave[$group] = $group;
			}
		}
		
		//////////////////
		//security check//
		//////////////////
		//remove all changes the current user is not allowed to do
		
		foreach($Bambus->UsersAndGroups->listSystemGroups() as $systemGroup)
		{
			if(!constant('BAMBUS_GRP_'.strtoupper($systemGroup)))
			{
				unset($join[$systemGroup]);
				unset($leave[$systemGroup]);
			}
		}
		//leave all groups
		$Bambus->UsersAndGroups->leaveGroups($victim, $leave);
		//set new groups
 		$Bambus->UsersAndGroups->joinGroups($victim, $join);
 		$Bambus->UsersAndGroups->setPrimaryGroup($victim, $post['primary_group']);
 		SNotificationCenter::alloc()->init()->report('message', 'group_assignment_saved');
	}
	
	///////////////////////////
	//save editor permissions//
	///////////////////////////
	
	if($post['action'] == 'save_editor_permissions' && BAMBUS_GRP_EDIT && $victim != BAMBUS_USER)
	{
		//list the applications
	    $Bambus->FileSystem->changeDir('systemApplication');
	    $Dir = opendir ('./'); 
	    $items = array();
	    while ($item = readdir ($Dir)) {
	        if((is_dir($item)) 
	        		&& (substr($item,0,1) != '.') 
	        		&& (strtolower(substr($item,-4)) == '.bap')){
	            $items[] = substr($item,0,-4);
	        }
	    }
	    closedir($Dir);
		$Bambus->FileSystem->returnToRootDir();
	    $grantPermission = array();
	    $rejectPermission = array();
	    foreach($items as $item)
	    {
	    	//all admins and the current user must have access to this app
	    	if(!(($victim == BAMBUS_USER || $Bambus->UsersAndGroups->isMemberOf($victim, 'Administrator')) && $item == BAMBUS_APPLICATION))
	    	{
		    	if(!empty($post['editor_'.md5($item)]) && ($Bambus->UsersAndGroups->hasPermission(BAMBUS_USER, $item) || BAMBUS_GRP_ADMINISTRATOR))
		    	{
		    		//we are allowed to change the value and we like this app -> activate it
		    		$grantPermission[] = $item;
		    	}
		    	elseif($Bambus->UsersAndGroups->hasPermission(BAMBUS_USER, $item) || BAMBUS_GRP_ADMINISTRATOR)
		    	{
		    		//changing allowed but this app stinks -> deactivate it
		    		$rejectPermission[] = $item;
		    	}
	    	}
	    	elseif($Bambus->UsersAndGroups->isMemberOf($victim, 'Administrator'))
	    	{
	    		//admins are foced to love me (the shiny user-administration)
	    		$grantPermission[] = $item;
	    	}
	    }
	    //set the beloved apps
	    $Bambus->UsersAndGroups->grantUserPermissions($victim, $grantPermission);
	    //send the others to hell
	    $Bambus->UsersAndGroups->rejectUserPermissions($victim, $rejectPermission);
	    SNotificationCenter::alloc()->init()->report('message', 'permissions_saved');
	}
	if($post['action'] == 'save_editor_group_permissions' && BAMBUS_GRP_EDIT)
	{
		SNotificationCenter::alloc()->init()->report('alert', 'not_implemented');
	}
	
	//update user & group data in database if it cares
	$dbNeedsUpdate = true;
	
}

if(!empty($get['_action']) && $get['_action'] == 'delete')
{
	
	
	
	////////////////
	//delete group//
	////////////////
	
	if($edit_mode == 'grp' && BAMBUS_GRP_DELETE)
	{
		if($Bambus->UsersAndGroups->isGroup($victim) && ! $Bambus->UsersAndGroups->isSystemGroup($victim))
		{
			$Bambus->UsersAndGroups->removeGroup($victim);
			SNotificationCenter::alloc()->init()->report('message', 'group_deleted');
			$edit_mode = 'usr';
			$victim = BAMBUS_USER;
		}
		else
		{
			SNotificationCenter::alloc()->init()->report('warning', 'this_group_cannot_be_deleted');
		}
		
	}
	///////////////
	//delete user//
	///////////////
	
	elseif($edit_mode == 'usr' && BAMBUS_GRP_DELETE)
	{
		if($Bambus->UsersAndGroups->isUser($victim) && $victim != BAMBUS_USER)
		{
			$result = $Bambus->UsersAndGroups->removeUser($victim);
			if($result)
			{
				SNotificationCenter::alloc()->init()->report('message', 'message');
				$victim = BAMBUS_USER;
			}
			elseif($result == -1)
			{
				SNotificationCenter::alloc()->init()->report('warning', 'you_cannot_delete_the_last_administrator');
			}
			else
			{
				SNotificationCenter::alloc()->init()->report('warning', 'failed_to_delete_this_user');
			}
		}
		else
		{
			SNotificationCenter::alloc()->init()->report('warning', 'you_do_not_have_the_permission_to_delete_this_user');
		}
	}
	$dbNeedsUpdate = true;
}



$users = $Bambus->UsersAndGroups->listUsers();
$groups = $Bambus->UsersAndGroups->listGroups();
if(count($users) > 0 || count($groups) > 0)
{
    ksort($users, SORT_STRING);
    ksort($groups, SORT_STRING);
    echo "\n<div id=\"OFD_Definition\">\n" .
			"<span id=\"OFD_Categories\">\n" .
				"<span>User</span>\n" .
				"<span>Administrator</span>\n" .//@todo navigation containing other navigations 
			"</span>\n" .
			"<span id=\"OFD_Items\">";

	//openFileDialog files
    foreach(array_keys($users) as $item)
	{
		$realname = $Bambus->UsersAndGroups->getRealName($item);
		$realname = ($item == BAMBUS_USER) ? 'You' : htmlentities($realname, ENT_QUOTES);
		$admin = $Bambus->UsersAndGroups->isMemberOf($item, 'Administrator');
		printf(
			'<a href="%s">' ."\n\t".
				'<span title="title">%s</span>' ."\n\t".
				'<span title="icon">%s</span>' ."\n\t".
				'<span title="description">%s</span>' ."\n\t".
				'<span title="category">%s</span>' ."\n".
			"</a>\n"
			,$Bambus->Linker->createQueryString(array('edit' => $item,'mode' => 'usr'))
			,htmlentities($item, ENT_QUOTES)
			,$Bambus->Gui->iconPath(($admin ? 'administrator' : 'user'), '', 'mimetype','medium')
			,$realname.' '
			,$admin ? 'Administrator' : 'User'
		);
	}
	echo "</span>\n</div>\n";
}

if(empty($get['tab']) || strpos($get['tab'],'manage_')===false)
{
	if($edit_mode == 'grp')
	{
		$EditingObject = sprintf('%s.%s', $victim, 'group');    
	}
	else
	{
		$EditingObject = sprintf('%s.%s', $victim, (($Bambus->UsersAndGroups->isMemberOf($victim, 'Administrator')) ? SLocalization::get('administrator') : 'user'));
	}
}
?>
<script language="JavaScript" type="text/javascript">
	var OBJ_ofd;
	OBJ_ofd = new CLASS_OpenFileDialog();
	OBJ_ofd.self = 'OBJ_ofd';
	OBJ_ofd.openIcon = '<?php echo $Bambus->Gui->iconPath('open', 'open', 'action', 'small'); ?>';
	OBJ_ofd.openTranslation = '<?php SLocalization::out('open'); ?>';
	OBJ_ofd.closeIcon = '<?php echo $Bambus->Gui->iconPath('delete', 'delete', 'action', 'small'); ?>';
	OBJ_ofd.statusText = '';
	OBJ_ofd.statusAnimation = '<?php echo $Bambus->Gui->iconPath('loading', 'loading', 'animation', 'extra-small'); ?>';
</script>
