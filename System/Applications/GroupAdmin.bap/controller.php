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
		if(!PAuthorisation::has('org.bambuscms.credentials.group.change'))
		{
		    foreach($SUsersAndGroups->listSystemGroups() as $systemGroup)
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
	$dbNeedsUpdate = true;
}

if(PAuthorisation::has('org.bambuscms.credentials.user.change') || PAuthorisation::has('org.bambuscms.credentials.group.change'))
{
	echo LGui::beginForm(array('edit' => ($edit_mode == 'usr' ? 'u:' : 'g:').$victim), 'documentform');
}
try{
	$panel = WSidePanel::alloc()->init();
	$panel->setMode(
	    WSidePanel::PERMISSIONS);
    if($SUsersAndGroups->isGroup($victim))
    {
        $panel->setTarget($victim, 'cms/'.($edit_mode == 'usr' ? 'user' : 'group'));
    }
	//echo $panel;
}
catch(Exception $e){
	echo $e->getTraceAsString();
}
$AppController = BAppController::getControllerForID('org.bambuscms.applications.groupmanager');
echo new WOpenDialog($AppController, $hasVictim);

?>
