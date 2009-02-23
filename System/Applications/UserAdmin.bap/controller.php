<?php
/************************************************
* Bambus CMS 
* Created:     03. Nov 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
$dbNeedsUpdate = false;
////////////////////
//what do we edit?//
////////////////////

$SUsersAndGroups = SUsersAndGroups::alloc()->init();


$edit = RURL::get('edit');
$mode = substr($edit,0,2) == 'g:' ? 'grp' : 'usr';
$edit = substr($edit,2);
$hasVictim = $edit;
if($mode == 'usr')
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
		$victim = null;
		$hasVictim = null;
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
	
	if(RSent::get('action') == 'edit_user_data' && PAuthorisation::has('org.bambuscms.credentials.user.change') && ($victim == PAuthentication::getUserID() || PAuthorisation::isInGroup('Administrator')))
	{
		$SUsersAndGroups->setUserRealName($victim, RSent::get('realName'));
		$SUsersAndGroups->setUserEmail($victim, RSent::get('email'));
		$SUsersAndGroups->setUserAttribute($victim, 'company', RSent::get('att_company'));
		SNotificationCenter::report('message', 'user_profile_saved');
		if(PAuthorisation::isInGroup('Administrator'))
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
	
	if(RSent::get('action') == 'create_new_group' && PAuthorisation::has('org.bambuscms.credentials.group.create'))
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
	
	if(RSent::get('action') == 'create_new_user' && PAuthorisation::has('org.bambuscms.credentials.user.create'))
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
			$SUsersAndGroups->setUserRealName($victim, RSent::get('new_user_real_name'));
			$SUsersAndGroups->setUserEmail($victim, RSent::get('new_user_email'));
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
			elseif(!RSent::hasValue('new_user_name'))
			{
				SNotificationCenter::report('warning', 'username_has_to_be_set');
			}
			elseif(!RSent::hasValue('new_user_password'))
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
	
	if(RSent::get('action') == 'save_assignment_of_groups' && PAuthorisation::has('org.bambuscms.credentials.user.change'))
	{
		$join = array();
		$leave = array();
		//you cant change your own system rights
		if($victim != PAuthentication::getUserID())
		{
			////////////////////////////////
			//change system group settings//
			////////////////////////////////
			
			if(!RSent::hasValue('join_group_Administrator'))
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
	
	if(RSent::get('action_2') == 'save_editor_permissions' && PAuthorisation::has('org.bambuscms.credentials.user.change') && $victim != PAuthentication::getUserID())
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
	    	if(!(($victim == PAuthentication::getUserID() || $SUsersAndGroups->isMemberOf($victim, 'Administrator')) && $item == LApplication::getName()))
	    	{
		    	if(RSent::hasValue('editor_'.md5($item)) && ($SUsersAndGroups->hasPermission(PAuthentication::getUserID(), $item) || PAuthorisation::isInGroup('Administrator')))
		    	{
		    		//we are allowed to change the value and we like this app -> activate it
		    		$grantPermission[] = $item;
		    	}
		    	elseif($SUsersAndGroups->hasPermission(PAuthentication::getUserID(), $item) || PAuthorisation::isInGroup('Administrator'))
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
	
	//update user & group data in database if it cares
	$dbNeedsUpdate = true;
	
}

if(RURL::get('_action') == 'delete')
{
	
	
	
	////////////////
	//delete group//
	////////////////
	
	if($edit_mode == 'grp' && PAuthorisation::has('org.bambuscms.credentials.group.delete'))
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
	
	elseif($edit_mode == 'usr' && PAuthorisation::has('org.bambuscms.credentials.user.delete'))
	{
		if($SUsersAndGroups->isUser($victim) && $victim != PAuthentication::getUserID())
		{
			$result = $SUsersAndGroups->removeUser($victim);
			if($result)
			{
				SNotificationCenter::report('message', 'user_deleted');
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
if(PAuthorisation::has('org.bambuscms.credentials.user.change') || PAuthorisation::has('org.bambuscms.credentials.group.change'))
{
	echo LGui::beginForm(array('edit' => ($edit_mode == 'usr' ? 'u:' : 'g:').$victim), 'documentform');
}
if($SUsersAndGroups->isUser($victim))
{
	try{
		$panel = new WSidePanel();
		$panel->setMode(
		    WSidePanel::PERMISSIONS);
	    $panel->setTarget($victim, 'cms/'.($edit_mode == 'usr' ? 'user' : 'group'));
		echo $panel;
	}
	catch(Exception $e){
		echo $e->getTraceAsString();
		
	}	
}
$AppController = BAppController::getControllerForID('org.bambuscms.applications.usereditor');
echo new WOpenDialog($AppController, $hasVictim);


?>