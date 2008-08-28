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
	echo $Bambus->Gui->beginForm();
	printf('<table id="addBox" class="hide" border="0" cellspacing="0" cellpadding="0">');
	printf("<tr valign=\"top\"><td class=\"addWrapper\"><a id=\"addUserLink\" class=\"activeAddButton\" href=\"javascript:addUser()\"><img src=\"%s\" alt=\"\" /></a><br /><a id=\"addGroupLink\" class=\"inactiveAddButton\" href=\"javascript:addGroup()\"><img src=\"%s\" alt=\"\" /></a></td><td>", $Bambus->Gui->iconPath('user', 'user', 'mimetype', 'medium'), $Bambus->Gui->iconPath('group', 'group', 'mimetype', 'medium'));
	echo $Bambus->Gui->hiddenInput('cptg_mode','mode');
	echo $Bambus->Gui->hiddenInput('cptg_new_user_name','edit', 'ucptg');
	echo $Bambus->Gui->hiddenInput('mode','usr', 'addmode');
	echo $Bambus->Gui->hiddenInput('action','create_new_user', 'actionInput');
	echo $Bambus->Gui->beginTable('add_user_table');
	printf('<tr><th colspan="2">%s</th></tr>', SLocalization::get('new_user'));
	printf('<tr><th class="tdx180">%s *</th><td>%s</td></tr>', SLocalization::get('username'), '<input type="text" name="new_user_name" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s *</th><td>%s</td></tr>', SLocalization::get('password'), '<input type="password" name="new_user_password" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s *</th><td>%s</td></tr>', SLocalization::get('retype_password'), '<input type="password" name="new_user_password_check" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', SLocalization::get('name_and_surname'), '<input type="text" name="new_user_name_and_surname" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', SLocalization::get('email'), '<input type="text" name="new_user_email" value="" class="fullinput" />');
	printf('<tr><th colspan="2"><input type="submit" value="%s" class="submitinput" /></th></tr>', SLocalization::get('create'));
	echo $Bambus->Gui->endTable();

	echo $Bambus->Gui->beginTable('add_group_table', 'hide');
	printf('<tr><th colspan="2">%s</th></tr>', SLocalization::get('new_group'));
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', SLocalization::get('name'), '<input type="text" name="new_group_name" value="" class="fullinput" />');
	echo $Bambus->Gui->hiddenInput('cptg_new_group_name','edit', 'gcptg');
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', SLocalization::get('description'), '<textarea name="new_group_description" rows="4" cols="40" class="smalleditarea"></textarea>');
	printf('<tr><th colspan="2"><input type="submit" value="%s" class="submitinput" /></th></tr>', SLocalization::get('create'));
	echo $Bambus->Gui->endTable();
	printf("</td></tr>");
	print('</table>');
	echo $Bambus->Gui->endForm();
}

if(BAMBUS_GRP_EDIT)
{
	echo $Bambus->Gui->beginForm(array('edit' => $victim), 'documentform');
	printf('<h2> %s</h2>'
		, htmlspecialchars($victim, ENT_QUOTES, 'utf-8'));
}

if($edit_mode == 'usr')
{
	///////////////////////
	//user administration//
	///////////////////////
	echo $Bambus->Gui->hiddenInput('action', 'edit_user_data');
	//what kind of edit do we have? admin|self|others
	$allowEdit = ($victim == BAMBUS_USER || BAMBUS_GRP_ADMINISTRATOR);
		$row = ($allowEdit)
 			? "<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td><input value=\"%s\" name=\"%s\" class=\"%s\" type=\"%s\" /></td></tr>\n"
			: "<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td>%s</td></tr>\n";
		$noEditRow = "<tr class=\"flip_%d\"><th class=\"left_th\">%s</th><td>%s</td></tr>\n";
	echo $Bambus->Gui->beginTable();
	
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
			,htmlentities($Bambus->UsersAndGroups->getRealName($victim))
			,'realName'
			,'fullinput'
			,'text'
		);
	//email
	printf(
			$row
			,1
			,SLocalization::get('email')
			,htmlentities($Bambus->UsersAndGroups->getEmail($victim))
			,'email'
			,'fullinput'
			,'text'
		);
	//company
	printf(
			$row
			,2
			,SLocalization::get('company')
			,htmlentities($Bambus->UsersAndGroups->getUserAttribute($victim, 'company'))
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
			$lastManagementLogin = $Bambus->UsersAndGroups->getUserAttribute($victim, 'last_management_login');
			$managementLoginCount = $Bambus->UsersAndGroups->getUserAttribute($victim, 'management_login_count');
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
	
	
	echo $Bambus->Gui->endTable();
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

	echo $Bambus->Gui->beginTable();
	echo $Bambus->Gui->tableHeader(array(SLocalization::get('description')));
	echo $Bambus->Gui->beginTableRow();
	echo htmlentities($Bambus->UsersAndGroups->getGroupDescription($victim));
	echo $Bambus->Gui->endTableRow();
	echo $Bambus->Gui->endTable();
	echo $Bambus->Gui->verticalSpace();
	echo $Bambus->Gui->beginTable();
	echo $Bambus->Gui->tableHeader(array(SLocalization::get('assigned_users')));
	echo $Bambus->Gui->beginTableRow();
	
	$assignedUsers = $Bambus->UsersAndGroups->listUsersOfGroup($victim);
	sort($assignedUsers, SORT_STRING);
	if(is_array($assignedUsers) && count($assignedUsers) > 0)
	{
		foreach($assignedUsers as $user)
		{
			printf(
					$urow
					,($Bambus->UsersAndGroups->isMemberOf($user, 'Administrator')) ? 'Gold' : ''
					,htmlentities($user)
				);
		}
	}
	
	echo '<br class="clear" />';
	echo $Bambus->Gui->endTableRow();
	echo $Bambus->Gui->endTable();
	
}

/////////////////////////////EX PERMS
echo '<br />';
$myDir = getcwd();//nice place... remember it
$Bambus->FileSystem->changeDir('systemApplication');
$Dir = opendir ('./'); 
$items = array();
$i = 0;
while ($item = readdir ($Dir)) 
{
	if(is_dir($item) && substr($item,0,1) != '.' && strtolower($Bambus->suffix($item)) == 'bap')
    {
    	//BambusApplicartion-Package
        $i++;
        list($name, $description, $icon, $pri) = array_values($Bambus->getBambusApplicationDescription($item.'/Application.xml'));
        $available[$pri.'_'.$i] = array('item' => $item,'name' => $name,'desc' => $description,'icon' => $icon, 'type' => 'application');
    }  
}
closedir($Dir);
$Bambus->FileSystem->returnToRootDir();
ksort($available);
$editor_arr = array();
foreach($available as $foo){
	list($editor, $name, $description, $icon, $type) = array_values($foo);
	$editor_arr[$editor] = SLocalization::get($name);
}
asort($editor_arr);
$flip = 2;
if($edit_mode == 'grp')
{
/////////////////////
//group information// 
/////////////////////

	$urow = <<<ROW
<div class="group%sMember">
	%s
</div>

ROW;

	echo $Bambus->Gui->beginTable();
	echo $Bambus->Gui->tableHeader(array(SLocalization::get('description')));
	echo $Bambus->Gui->beginTableRow();
	echo htmlentities($Bambus->UsersAndGroups->getGroupDescription($victim));
	echo $Bambus->Gui->endTableRow();
	echo $Bambus->Gui->endTable();
	echo $Bambus->Gui->verticalSpace();
	echo $Bambus->Gui->beginTable();
	echo $Bambus->Gui->tableHeader(array(SLocalization::get('assigned_users')));
	echo $Bambus->Gui->beginTableRow();
	
	$assignedUsers = $Bambus->UsersAndGroups->listUsersOfGroup($victim);
	sort($assignedUsers, SORT_STRING);
	if(is_array($assignedUsers) && count($assignedUsers) > 0)
	{
		foreach($assignedUsers as $user)
		{
			printf(
					$urow
					,($Bambus->UsersAndGroups->isMemberOf($user, 'Administrator')) ? 'Gold' : ''
					,htmlentities($user)
				);
		}
	}
	
	echo '<br class="clear" />';
	echo $Bambus->Gui->endTableRow();
	echo $Bambus->Gui->endTable();
}
else
{
	echo $Bambus->Gui->verticalSpace();
	echo $Bambus->Gui->beginTable();
	printf('<tr><th></th><th colspan="2">%s</th></tr>', SLocalization::get('editor_permissions'));
	$line = <<<EOX
			<tr class="flip_%s">
				<th class="tdicon">
					<input type="checkbox" %s id="ckbx_%s" name="editor_%s" />
				</th>
				<td>
					<label for="ckbx_%s">
						%s
						%s
						<br />
						<small>
							<i>
								<p>
									%s
								</p>
								<p>	
									%s
								</p>
							</i>
					</small>
					</label>
				</td>
			</tr>
	
EOX;
	
	foreach($available as $editor){
	    $flip = ($flip == '1') ? '2' : '1';
	    if(!empty($editor))
	    {
	        $app_name = substr($editor['item'],0,((strlen($Bambus->suffix($editor['item']))+1) * -1));
	        $id = md5($app_name);
	        $is_admin = $Bambus->UsersAndGroups->isMemberOf($victim, 'Administrator');
	        //printf('%s %s an administrator; ', $victim, ($is_admin) ? 'is' : 'is not');
	        $checked = ($is_admin || $Bambus->UsersAndGroups->hasPermission($victim, $app_name)) ? ' checked="checked"' : '';
	        $disabled = ($is_admin || ($app_name == BAMBUS_APPLICATION && $victim == BAMBUS_USER)) ? ' disabled="disabled"' : '';
	        	printf(
	        		$line,
	        		$flip,
	        		$checked.$disabled,
	        		$id,
	        		$id,
	        		$id,
	        		$Bambus->Gui->icon($editor['icon'],'','app'),
	        		SLocalization::get($editor['name']),
	        		SLocalization::get($editor['desc']),
	        		''
	        	);
	    }
	}
	echo $Bambus->Gui->endTable();
	echo $Bambus->Gui->verticalSpace();
}
/////////////////////////////EOF EX PERMS

if(BAMBUS_GRP_EDIT)
{
    echo $Bambus->Gui->endForm();
}
?>