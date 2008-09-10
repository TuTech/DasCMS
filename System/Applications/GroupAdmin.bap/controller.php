<?php
/************************************************
* Bambus CMS 
* Created:     03. Nov 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
$dbNeedsUpdate = false;
////////////////////
//what do we edit?//
////////////////////

$SUsersAndGroups = SUsersAndGroups::alloc()->init();


$edit = RURL::get('edit');
if(!RURL::has('mode') || RURL::get('mode') == 'usr')
{
	/////////
	//users//
	/////////
	
	$edit_mode = 'usr';
	$victim = (($SUsersAndGroups->isUser($edit)) ? $edit : PAuthentication::getUserID());
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
		$victim = PAuthentication::getUserID();
		$edit_mode = 'usr';
	}
}

//////////////////
//process inputs//
//////////////////

if(RSent::hasValue('action'))
{
	/////////////////////
	//edit user profile//
	/////////////////////
	
	if(RSent::get('action') == 'edit_user_data' && BAMBUS_GRP_EDIT && ($victim == PAuthentication::getUserID() || BAMBUS_GRP_ADMINISTRATOR))
	{
		$SUsersAndGroups->setUserRealName($victim, RSent::get('realName'));
		$SUsersAndGroups->setUserEmail($victim, RSent::get('email'));
		$SUsersAndGroups->setUserAttribute($victim, 'company', RSent::get('att_company'));
		SNotificationCenter::report('message', 'user_profile_saved');
		if(BAMBUS_GRP_ADMINISTRATOR)
		{
			if(RSent::hasValue('adm_set_password') && RSent::hasValue('adm_set_password_confirm') && RSent::get('adm_set_password_confirm') == RSent::get('adm_set_password'))
			{
				$SUsersAndGroups->setUserPassword($victim, RSent::get('adm_set_password'));
			}
			if(RSent::hasValue('adm_set_password_confirm') && RSent::hasValue('adm_set_password') && md5(RSent::get('adm_set_password_confirm')) == $SUsersAndGroups->getPasswordHash($victim))
			{
				SNotificationCenter::report('message', 'new_password_set');
			}
			elseif(RSent::hasValue('adm_set_password'))
			{
				SNotificationCenter::report('alert', 'passwords_do_not_match');
			}
		}
		else
		{
			if(
				(RSent::hasValue('change_password_from_old') && $SUsersAndGroups->isValidUser($victim, RSent::get('change_password_from_old')))
				&&
				(RSent::hasValue('change_password_to_new') && RSent::hasValue('change_password_confirm') && RSent::get('change_password_to_new') == RSent::get('change_password_confirm'))
			  )
			{
				$SUsersAndGroups->setUserPassword($victim, RSent::get('change_password_to_new'));
				SNotificationCenter::report('message', 'password_changed');
			}
			elseif(RSent::hasValue('change_password_from_old') && $SUsersAndGroups->isValidUser($victim, RSent::get('change_password_from_old')))
			{
				SNotificationCenter::report('alert', 'passwords_do_not_match');
			}
		}
	}

	/////////////////
	//group actions//
	/////////////////
	
	if(RSent::get('action') == 'create_new_group' && BAMBUS_GRP_CREATE)
	{
		if(RSent::hasValue('new_group_name') && RSent::has('new_group_description'))
		{
			$SUsersAndGroups->addGroup(RSent::get('new_group_name'), RSent::get('new_group_description'));
			SNotificationCenter::report('message', 'group_created');
			$edit = RSent::get('new_group_name');
			$victim = RSent::get('new_group_name');
			$edit_mode = 'grp';
		}
		else
		{
			SNotificationCenter::report('warning', 'no_group_name_specified');
		}
	}
	////////////////
	//user actions//
	////////////////
	
	if(RSent::get('action') == 'create_new_user' && BAMBUS_GRP_CREATE)
	{
		if(
		  	(RSent::hasValue('new_user_name')) &&
		  	(RSent::hasValue('new_user_password')) &&
		  	(RSent::hasValue('new_user_password_check')) &&
		  	(!$SUsersAndGroups->isUser(RSent::get('new_user_name'))) &&
		  	(RSent::get('new_user_password') == RSent::get('new_user_password_check'))
		  )
		{
			///////////////
			//create user//
			///////////////
			
			$SUsersAndGroups->addUser(RSent::get('new_user_name'), RSent::get('new_user_password'), RSent::get('new_user_name_and_surname'), RSent::get('new_user_email'));
			SNotificationCenter::report('message', 'user_created');
			$victim = RSent::get('new_user_name');
			$edit_mode = 'usr';
			SLink::set('edit', $victim);
			SLink::set('mode', 'usr');
		}
		else
		{
			/////////////////////////
			//display error message//
			/////////////////////////
			
			if(RSent::get('new_user_password') != RSent::get('new_user_password_check'))
			{
				SNotificationCenter::report('warning', 'passwords_not_equal');
			}
			elseif($SUsersAndGroups->isUser(RSent::get('new_user_name')))
			{
				SNotificationCenter::report('warning', 'user_already_exists');
			}
			elseif(RSent::hasValue('new_user_name'))
			{
				SNotificationCenter::report('warning', 'username_has_to_be_set');
			}
			elseif(RSent::hasValue('new_user_password'))
			{
				SNotificationCenter::report('warning', 'password_has_to_be_set');
			}
			else
			{
				SNotificationCenter::report('warning', 'failed_to_create_user');
			}
		}
	}
	
	/////////////////////////
	//save group assignment//
	/////////////////////////
	
	if(RSent::get('action') == 'save_assignment_of_groups' && BAMBUS_GRP_EDIT)
	{
		$join = array();
		$leave = array();
		//you cant change your own system rights
		if($victim != PAuthentication::getUserID())
		{
			////////////////////////////////
			//change system group settings//
			////////////////////////////////
			
			if(RSent::hasValue('join_group_Administrator'))
			{
				//no administrator 
				foreach($SUsersAndGroups->listSystemGroups() as $systemGroup)
				{
					if(RSent::hasValue('join_group_'.md5($systemGroup)))
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
			$groups = $SUsersAndGroups->listGroupsOfUser(PAuthentication::getUserID());
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
			if(RSent::hasValue('join_group_'.md5($group)))
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
 		$SUsersAndGroups->setPrimaryGroup($victim, RSent::get('primary_group'));
 		SNotificationCenter::report('message', 'group_assignment_saved');
	}
	
	///////////////////////////
	//save editor permissions//
	///////////////////////////
	
	if(RSent::get('action') == 'save_editor_permissions' && BAMBUS_GRP_EDIT && $victim != PAuthentication::getUserID())
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
	    	if(!(($victim == PAuthentication::getUserID() || $SUsersAndGroups->isMemberOf($victim, 'Administrator')) && $item == BAMBUS_APPLICATION))
	    	{
		    	if(RSent::hasValue('editor_'.md5($item)) && ($SUsersAndGroups->hasPermission(PAuthentication::getUserID(), $item) || BAMBUS_GRP_ADMINISTRATOR))
		    	{
		    		//we are allowed to change the value and we like this app -> activate it
		    		$grantPermission[] = $item;
		    	}
		    	elseif($SUsersAndGroups->hasPermission(PAuthentication::getUserID(), $item) || BAMBUS_GRP_ADMINISTRATOR)
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
	    SNotificationCenter::report('message', 'permissions_saved');
	}
	if(RSent::get('action') == 'save_editor_group_permissions' && BAMBUS_GRP_EDIT)
	{
		SNotificationCenter::report('alert', 'not_implemented');
	}
	
	//update user & group data in database if it cares
	$dbNeedsUpdate = true;
	
}

if(RURL::get('_action') == 'delete')
{
	
	
	
	////////////////
	//delete group//
	////////////////
	
	if($edit_mode == 'grp' && BAMBUS_GRP_DELETE)
	{
		if($SUsersAndGroups->isGroup($victim) && ! $SUsersAndGroups->isSystemGroup($victim))
		{
			$SUsersAndGroups->removeGroup($victim);
			SNotificationCenter::report('message', 'group_deleted');
			$edit_mode = 'usr';
			$victim = PAuthentication::getUserID();
		}
		else
		{
			SNotificationCenter::report('warning', 'this_group_cannot_be_deleted');
		}
		
	}
	///////////////
	//delete user//
	///////////////
	
	elseif($edit_mode == 'usr' && BAMBUS_GRP_DELETE)
	{
		if($SUsersAndGroups->isUser($victim) && $victim != PAuthentication::getUserID())
		{
			$result = $SUsersAndGroups->removeUser($victim);
			if($result)
			{
				SNotificationCenter::report('message', 'message');
				$victim = PAuthentication::getUserID();
			}
			elseif($result == -1)
			{
				SNotificationCenter::report('warning', 'you_cannot_delete_the_last_administrator');
			}
			else
			{
				SNotificationCenter::report('warning', 'failed_to_delete_this_user');
			}
		}
		else
		{
			SNotificationCenter::report('warning', 'you_do_not_have_the_permission_to_delete_this_user');
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
				"<span>Group</span>\n" .
				"<span>User</span>\n" .
				"<span>Administrator</span>\n" .
    		"</span>\n" .
			"<span id=\"OFD_Items\">";

    foreach(array_keys($groups) as $item)
	{
		if($SUsersAndGroups->isSystemGroup($item))
		{
			continue;
		}
		printf(
			'<a href="%s">' ."\n\t".
				'<span title="title">%s</span>' ."\n\t".
				'<span title="icon">%s</span>' ."\n\t".
				'<span title="description">%s</span>' ."\n\t".
				'<span title="category">%s</span>' ."\n".
			"</a>\n"
			,SLink::link(array('edit' => $item,'mode' => 'grp'))
			,htmlentities($item, ENT_QUOTES)
			,WIcon::pathFor('group', 'mimetype',WIcon::MEDIUM)
			,' '
			,'Group'
		);
	}
    foreach(array_keys($users) as $item)
	{
		$realname = $SUsersAndGroups->getRealName($item);
		$realname = ($item == PAuthentication::getUserID()) ? 'You' : htmlentities($realname, ENT_QUOTES);
		$admin = $SUsersAndGroups->isMemberOf($item, 'Administrator');
		printf(
			'<a href="%s">' ."\n\t".
				'<span title="title">%s</span>' ."\n\t".
				'<span title="icon">%s</span>' ."\n\t".
				'<span title="description">%s</span>' ."\n\t".
				'<span title="category">%s</span>' ."\n".
			"</a>\n"
			,SLink::link(array('edit' => $item,'mode' => 'usr'))
			,htmlentities($item, ENT_QUOTES)
			,WIcon::pathFor(($admin ? 'administrator' : 'user'), 'mimetype',WIcon::MEDIUM)
			,$realname.' '
			,$admin ? 'Administrator' : 'User'
		);
	}
	echo "</span>\n</div>\n";
}

if(strpos(RURL::get('tab'),'manage_')===false)
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
	OBJ_ofd.openIcon = '<?php echo WIcon::pathFor('open') ?>';
	OBJ_ofd.openTranslation = '<?php SLocalization::out('open'); ?>';
	OBJ_ofd.closeIcon = '<?php echo WIcon::pathFor('delete') ?>';
	OBJ_ofd.statusText = '';
	OBJ_ofd.statusAnimation = '<?php echo  WIcon::pathFor('loading', 'animation', WIcon::EXTRA_SMALL);  ?>';
</script>
