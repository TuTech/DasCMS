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

$SUsersAndGroups = SUsersAndGroups::alloc()->init();


$edit = (!isset($get['edit'])) ? '' : $get['edit'];
if(empty($get['mode']) || $get['mode'] == 'usr')
{
	/////////
	//users//
	/////////
	
	$edit_mode = 'usr';
	$victim = (($SUsersAndGroups->isUser($edit)) ? $edit : BAMBUS_USER);
}
else
{	
	//////////
	//groups//
	//////////
	
	if($SUsersAndGroups->isGroup($edit))
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
		$SUsersAndGroups->setUserRealName($victim, $post['realName']);
		$SUsersAndGroups->setUserEmail($victim, $post['email']);
		$SUsersAndGroups->setUserAttribute($victim, 'company', $post['att_company']);
		SNotificationCenter::alloc()->init()->report('message', 'user_profile_saved');
		if(BAMBUS_GRP_ADMINISTRATOR)
		{
			if(!empty($post['adm_set_password']) && !empty($post['adm_set_password_confirm']) &&$post['adm_set_password_confirm'] == $post['adm_set_password'])
			{
				$SUsersAndGroups->setUserPassword($victim, $post['adm_set_password']);
			}
			if(!empty($post['adm_set_password_confirm']) &&!empty($post['adm_set_password']) && md5($post['adm_set_password_confirm']) == $SUsersAndGroups->getPasswordHash($victim))
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
				(!empty($post['change_password_from_old']) && $SUsersAndGroups->isValidUser($victim, $post['change_password_from_old']))
				&&
				(!empty($post['change_password_to_new']) && !empty($post['change_password_confirm']) && $post['change_password_to_new'] == $post['change_password_confirm'])
			  )
			{
				$SUsersAndGroups->setUserPassword($victim, $post['change_password_to_new']);
				SNotificationCenter::alloc()->init()->report('message', 'password_changed');
			}
			elseif(!empty($post['change_password_from_old']) && $SUsersAndGroups->isValidUser($victim, $post['change_password_from_old']))
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
			$SUsersAndGroups->addGroup($post['new_group_name'], $post['new_group_description']);
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
		  	(!$SUsersAndGroups->isUser($post['new_user_name'])) &&
		  	($post['new_user_password'] == $post['new_user_password_check'])
		  )
		{
			///////////////
			//create user//
			///////////////
			
			$SUsersAndGroups->addUser($post['new_user_name'], $post['new_user_password'], $post['new_user_name_and_surname'], $post['new_user_email']);
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
			elseif($SUsersAndGroups->isUser($post['new_user_name']))
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
				foreach($SUsersAndGroups->listSystemGroups() as $systemGroup)
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
				foreach($SUsersAndGroups->listSystemGroups() as $systemGroup)
				{
					$join[$systemGroup] = $systemGroup;
				}			
			}
		}
		else
		{
			$groups = $SUsersAndGroups->listGroupsOfUser(BAMBUS_USER);
			foreach($groups as $group)
			{	
				if($SUsersAndGroups->isSystemGroup($group))
					$join[$group] = $group;
			}
		}
		
		////////////////////////////////
		//change custom group settings//
		////////////////////////////////
		
		foreach(array_keys($SUsersAndGroups->listGroups()) as $group)
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
		
		foreach($SUsersAndGroups->listSystemGroups() as $systemGroup)
		{
			if(!constant('BAMBUS_GRP_'.strtoupper($systemGroup)))
			{
				unset($join[$systemGroup]);
				unset($leave[$systemGroup]);
			}
		}
		//leave all groups
		$SUsersAndGroups->leaveGroups($victim, $leave);
		//set new groups
 		$SUsersAndGroups->joinGroups($victim, $join);
 		$SUsersAndGroups->setPrimaryGroup($victim, $post['primary_group']);
 		SNotificationCenter::alloc()->init()->report('message', 'group_assignment_saved');
	}
	
	///////////////////////////
	//save editor permissions//
	///////////////////////////
	
	if($post['action'] == 'save_editor_permissions' && BAMBUS_GRP_EDIT && $victim != BAMBUS_USER)
	{
		//list the applications
	    chdir(SPath::SYSTEM_APPLICATIONS);
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
		chdir(constant('BAMBUS_CMS_ROOTDIR'));
	    $grantPermission = array();
	    $rejectPermission = array();
	    foreach($items as $item)
	    {
	    	//all admins and the current user must have access to this app
	    	if(!(($victim == BAMBUS_USER || $SUsersAndGroups->isMemberOf($victim, 'Administrator')) && $item == BAMBUS_APPLICATION))
	    	{
		    	if(!empty($post['editor_'.md5($item)]) && ($SUsersAndGroups->hasPermission(BAMBUS_USER, $item) || BAMBUS_GRP_ADMINISTRATOR))
		    	{
		    		//we are allowed to change the value and we like this app -> activate it
		    		$grantPermission[] = $item;
		    	}
		    	elseif($SUsersAndGroups->hasPermission(BAMBUS_USER, $item) || BAMBUS_GRP_ADMINISTRATOR)
		    	{
		    		//changing allowed but this app stinks -> deactivate it
		    		$rejectPermission[] = $item;
		    	}
	    	}
	    	elseif($SUsersAndGroups->isMemberOf($victim, 'Administrator'))
	    	{
	    		//admins are foced to love me (the shiny user-administration)
	    		$grantPermission[] = $item;
	    	}
	    }
	    //set the beloved apps
	    $SUsersAndGroups->grantUserPermissions($victim, $grantPermission);
	    //send the others to hell
	    $SUsersAndGroups->rejectUserPermissions($victim, $rejectPermission);
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
		if($SUsersAndGroups->isGroup($victim) && ! $SUsersAndGroups->isSystemGroup($victim))
		{
			$SUsersAndGroups->removeGroup($victim);
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
		if($SUsersAndGroups->isUser($victim) && $victim != BAMBUS_USER)
		{
			$result = $SUsersAndGroups->removeUser($victim);
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



$users = $SUsersAndGroups->listUsers();
$groups = $SUsersAndGroups->listGroups();
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
		$realname = $SUsersAndGroups->getRealName($item);
		$realname = ($item == BAMBUS_USER) ? 'You' : htmlentities($realname, ENT_QUOTES);
		$admin = $SUsersAndGroups->isMemberOf($item, 'Administrator');
		printf(
			'<a href="%s">' ."\n\t".
				'<span title="title">%s</span>' ."\n\t".
				'<span title="icon">%s</span>' ."\n\t".
				'<span title="description">%s</span>' ."\n\t".
				'<span title="category">%s</span>' ."\n".
			"</a>\n"
			,$Bambus->Linker->createQueryString(array('edit' => $item,'mode' => 'usr'))
			,htmlentities($item, ENT_QUOTES)
			,WIcon::pathFor(($admin ? 'administrator' : 'user'), 'mimetype',WIcon::MEDIUM)
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
		$EditingObject = sprintf('%s.%s', $victim, (($SUsersAndGroups->isMemberOf($victim, 'Administrator')) ? SLocalization::get('administrator') : 'user'));
	}
}
?>
<script language="JavaScript" type="text/javascript">
	var OBJ_ofd;
	OBJ_ofd = new CLASS_OpenFileDialog();
	OBJ_ofd.self = 'OBJ_ofd';
	OBJ_ofd.openIcon = '<?php echo WIcon::pathFor('open'); ?>';
	OBJ_ofd.openTranslation = '<?php SLocalization::out('open'); ?>';
	OBJ_ofd.closeIcon = '<?php echo WIcon::pathFor('delete'); ?>';
	OBJ_ofd.statusText = '';
	OBJ_ofd.statusAnimation = '<?php echo  WIcon::pathFor('loading', 'animation', WIcon::EXTRA_SMALL);  ?>';
</script>
