<?php
/************************************************
* Bambus CMS 
* Created:     03. Nov 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');

////////////////////
//create user form//
////////////////////
//TODO: rewrite
if(BAMBUS_GRP_CREATE)
{
	echo LGui::beginForm();
	printf('<table id="addBox" class="hide" border="0" cellspacing="0" cellpadding="0">');
	printf("<tr valign=\"top\"><td class=\"addWrapper\"><a id=\"addUserLink\" class=\"activeAddButton\" href=\"javascript:addUser()\"><img src=\"%s\" alt=\"\" /></a><br /><a id=\"addGroupLink\" class=\"inactiveAddButton\" href=\"javascript:addGroup()\"><img src=\"%s\" alt=\"\" /></a></td><td>", WIcon::pathFor('user', 'mimetype', WIcon::MEDIUM), WIcon::pathFor('group', 'mimetype', WIcon::MEDIUM));
	echo LGui::hiddenInput('cptg_mode','mode');
	echo LGui::hiddenInput('cptg_new_user_name','edit', 'ucptg');
	echo LGui::hiddenInput('mode','usr', 'addmode');
	echo LGui::hiddenInput('action','create_new_user', 'actionInput');
	echo LGui::beginTable('add_user_table');
	printf('<tr><th colspan="2">%s</th></tr>', SLocalization::get('new_user'));
	printf('<tr><th class="tdx180">%s *</th><td>%s</td></tr>', SLocalization::get('username'), '<input type="text" name="new_user_name" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s *</th><td>%s</td></tr>', SLocalization::get('password'), '<input type="password" name="new_user_password" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s *</th><td>%s</td></tr>', SLocalization::get('retype_password'), '<input type="password" name="new_user_password_check" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', SLocalization::get('name_and_surname'), '<input type="text" name="new_user_name_and_surname" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', SLocalization::get('email'), '<input type="text" name="new_user_email" value="" class="fullinput" />');
	printf('<tr><th colspan="2"><input type="submit" value="%s" class="submitinput" /></th></tr>', SLocalization::get('create'));
	echo LGui::endTable();

	echo LGui::beginTable('add_group_table', 'hide');
	printf('<tr><th colspan="2">%s</th></tr>', SLocalization::get('new_group'));
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', SLocalization::get('name'), '<input type="text" name="new_group_name" value="" class="fullinput" />');
	echo LGui::hiddenInput('cptg_new_group_name','edit', 'gcptg');
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', SLocalization::get('description'), '<textarea name="new_group_description" rows="4" cols="40" class="smalleditarea"></textarea>');
	printf('<tr><th colspan="2"><input type="submit" value="%s" class="submitinput" /></th></tr>', SLocalization::get('create'));
	echo LGui::endTable();
	printf("</td></tr>");
	print('</table>');
	echo LGui::endForm();
}

if(BAMBUS_GRP_EDIT)
{
	echo LGui::beginForm(array('edit' => $victim), 'documentform');
	printf('<h2>%s: %s</h2>'
		,SLocalization::get(($edit_mode == 'usr') ? 'user' : 'group')
		, htmlspecialchars($victim, ENT_QUOTES, 'utf-8'));
}

$SUsersAndGroups = SUsersAndGroups::alloc()->init();

if($edit_mode == 'usr')
{
	///////////////////////
	//user administration//
	///////////////////////
	echo LGui::hiddenInput('action', 'edit_user_data');
	//what kind of edit do we have? admin|self|others
	$allowEdit = ($victim == PAuthentication::getUserID() || BAMBUS_GRP_ADMINISTRATOR);
		$row = ($allowEdit)
 			? "<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td><input value=\"%s\" name=\"%s\" class=\"%s\" type=\"%s\" /></td></tr>\n"
			: "<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td>%s</td></tr>\n";
		$noEditRow = "<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td>%s</td></tr>\n";
	echo LGui::beginTable();
	
	printf("<tr><th colspan=\"2\">%s</th></tr>\n", SLocalization::get(($allowEdit) ? 'edit_user_profile' : 'view_user_profile'));
	//user name
	printf(
			$noEditRow
			,1
			,SLocalization::get('username')
			,htmlentities($victim)
		);
	//real name of the user
	printf(
			$row
			,2
			,SLocalization::get('name')
			,htmlentities($SUsersAndGroups->getRealName($victim))
			,'realName'
			,'fullinput'
			,'text'
		);
	//email
	printf(
			$row
			,1
			,SLocalization::get('email')
			,htmlentities($SUsersAndGroups->getEmail($victim))
			,'email'
			,'fullinput'
			,'text'
		);
	//company
	printf(
			$row
			,2
			,SLocalization::get('company')
			,htmlentities($SUsersAndGroups->getUserAttribute($victim, 'company'))
			,'att_company'
			,'fullinput'
			,'text'
		);
	if($allowEdit)
	{
		if(BAMBUS_GRP_ADMINISTRATOR)
		{
			/////////
			//admin//
			/////////
			printf("<tr><th colspan=\"2\">%s</th></tr>\n", SLocalization::get('set_password'));
			printf(
					"<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td><input value=\"\" name=\"%s\" class=\"%s\" type=\"%s\" /></td></tr>\n"
					,1
					,SLocalization::get('set_new_password')
					,'adm_set_password'
					,'fullinput'
					,'password'
				);
			printf(
					"<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td><input value=\"\" name=\"%s\" class=\"%s\" type=\"%s\" /></td></tr>\n"
					,2
					,SLocalization::get('confirm_new_password')
					,'adm_set_password_confirm'
					,'fullinput'
					,'password'
				);
			printf("<tr><th colspan=\"2\">%s</th></tr>\n", SLocalization::get('login_information'));
			$lastManagementLogin = $SUsersAndGroups->getUserAttribute($victim, 'last_management_login');
			$managementLoginCount = $SUsersAndGroups->getUserAttribute($victim, 'management_login_count');
			printf(
					$noEditRow
					,2
					,SLocalization::get('last_management_login')
					,(empty($lastManagementLogin)) ? SLocalization::get('this_user_has_not_logged_in_yet') : date('r', $lastManagementLogin)
				);
			printf(
					$noEditRow
					,1
					,SLocalization::get('number_of_management_logins')
					,(empty($lastManagementLogin)) ? 0 : htmlentities($managementLoginCount)
				);
		}
		else
		{	
			//////////////////////////////
			//user edits his own profile//
			//////////////////////////////
			printf("<tr><th colspan=\"2\">%s</th></tr>\n", SLocalization::get('change_my_password'));
			
			printf(
					"<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td><input value=\"\" name=\"%s\" class=\"%s\" type=\"%s\" /></td></tr>\n"
					,1
					,SLocalization::get('old_password')
					,'change_password_from_old'
					,'fullinput'
					,'password'
				);
			printf(
					"<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td><input value=\"\" name=\"%s\" class=\"%s\" type=\"%s\" /></td></tr>\n"
					,2
					,SLocalization::get('new_password')
					,'change_password_to_new'
					,'fullinput'
					,'password'
				);
			printf(
					"<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td><input value=\"\" name=\"%s\" class=\"%s\" type=\"%s\" /></td></tr>\n"
					,1
					,SLocalization::get('confirm_new_password')
					,'change_password_confirm'
					,'fullinput'
					,'password'
				);
		}
	}
	
	
	echo LGui::endTable();
}
else
{
/////////////////////
//group information// 
/////////////////////

	$urow = <<<ROW
<div class="group%sMember">
	%s
</div>

ROW;

	echo LGui::beginTable();
	echo LGui::tableHeader(array(SLocalization::get('description')));
	echo LGui::beginTableRow();
	echo htmlentities($SUsersAndGroups->getGroupDescription($victim));
	echo LGui::endTableRow();
	echo LGui::endTable();
	echo LGui::verticalSpace();
	echo LGui::beginTable();
	echo LGui::tableHeader(array(SLocalization::get('assigned_users')));
	echo LGui::beginTableRow();
	
	$assignedUsers = $SUsersAndGroups->listUsersOfGroup($victim);
	sort($assignedUsers, SORT_STRING);
	if(is_array($assignedUsers) && count($assignedUsers) > 0)
	{
		foreach($assignedUsers as $user)
		{
			printf(
					$urow
					,($SUsersAndGroups->isMemberOf($user, 'Administrator')) ? 'Gold' : ''
					,htmlentities($user)
				);
		}
	}
	
	echo '<br class="clear" />';
	echo LGui::endTableRow();
	echo LGui::endTable();
	
}

if(BAMBUS_GRP_EDIT)
{
    echo LGui::endForm();
}
?>