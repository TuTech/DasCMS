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
	printf('<tr><th colspan="2">%s</th></tr>', $Bambus->Translation->sayThis('new_user'));
	printf('<tr><th class="tdx180">%s *</th><td>%s</td></tr>', $Bambus->Translation->sayThis('username'), '<input type="text" name="new_user_name" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s *</th><td>%s</td></tr>', $Bambus->Translation->sayThis('password'), '<input type="password" name="new_user_password" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s *</th><td>%s</td></tr>', $Bambus->Translation->sayThis('retype_password'), '<input type="password" name="new_user_password_check" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', $Bambus->Translation->sayThis('name_and_surname'), '<input type="text" name="new_user_name_and_surname" value="" class="fullinput" />');
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', $Bambus->Translation->sayThis('email'), '<input type="text" name="new_user_email" value="" class="fullinput" />');
	printf('<tr><th colspan="2"><input type="submit" value="%s" class="submitinput" /></th></tr>', $Bambus->Translation->sayThis('create'));
	echo $Bambus->Gui->endTable();

	echo $Bambus->Gui->beginTable('add_group_table', 'hide');
	printf('<tr><th colspan="2">%s</th></tr>', $Bambus->Translation->sayThis('new_group'));
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', $Bambus->Translation->sayThis('name'), '<input type="text" name="new_group_name" value="" class="fullinput" />');
	echo $Bambus->Gui->hiddenInput('cptg_new_group_name','edit', 'gcptg');
	printf('<tr><th class="tdx180">%s</th><td>%s</td></tr>', $Bambus->Translation->sayThis('description'), '<textarea name="new_group_description" rows="4" cols="40" class="smalleditarea"></textarea>');
	printf('<tr><th colspan="2"><input type="submit" value="%s" class="submitinput" /></th></tr>', $Bambus->Translation->sayThis('create'));
	echo $Bambus->Gui->endTable();
	printf("</td></tr>");
	print('</table>');
	echo $Bambus->Gui->endForm();
}
if(BAMBUS_GRP_EDIT)
{
	echo $Bambus->Gui->beginForm(array('edit' => $victim), 'documentform');
	printf('<h2>%s: %s</h2>'
		,$Bambus->Translation->treturn(($edit_mode == 'usr') ? 'user' : 'group')
		, htmlspecialchars($victim, ENT_QUOTES, 'utf-8'));
}

if($edit_mode == 'usr')
{    
    //////////////////////
    //editor permissions//
    //////////////////////
    
    echo $Bambus->Gui->verticalSpace();
    echo $Bambus->Gui->hiddenInput('action', 'save_assignment_of_groups');
    echo $Bambus->Gui->beginTable();
    printf('<tr><th class="tdicon">&nbsp;</th><th>%s</th></tr>',$Bambus->Translation->sayThis('assignment_of_groups'));
	$id = 1;
	$flip = 2;
	$groups = $Bambus->UsersAndGroups->listGroups();
	foreach(array_keys($groups) as $group)
	{
		if(!$Bambus->UsersAndGroups->isSystemGroup($group))
		{
			$flip = ($flip == '1') ? '2' : '1';
			$desc = (empty($groups[$group])) ? '' : '<br /><small><i><p>'.htmlentities($groups[$group]).'</p></i></small>';
			printf(
				'<tr><th class="tdicon"><input id="group_%d" type="checkbox" name="join_group_%s" %s/></th>'.
					'<td class="flip_%s"><label for="group_%d"><img src="%s" alt="" /> %s</label></td></tr>'
				,$id
				,md5($group)
				,(($Bambus->UsersAndGroups->isMemberOf($victim, $group)) ? 'checked="checked" ' : '')
				,$flip
				,$id
				,$Bambus->Gui->iconPath('group', 'group', 'mimetype', 'small')
				,htmlentities($group).$desc
			);
			$id++;
		}
	}
	if($id == 1)
	{
		printf('<tr><th class="tdicon"></th><td>%s</td></tr>',$Bambus->Translation->sayThis('no_userdefined_groups'));
	}
	echo $Bambus->Gui->endTable();
	echo $Bambus->Gui->verticalSpace();
	$usergroups = $Bambus->UsersAndGroups->listGroupsOfUser($victim);
	
	/////////////////
	//primary group//
	/////////////////
	
	echo $Bambus->Gui->beginTable();
	printf('<tr><th>%s</th></tr><tr><td><select class="selectinput" name="primary_group">', $Bambus->Translation->sayThis('primary_group'));
	$grparray = array('' => $Bambus->Translation->sayThis('none'));
	$selected = '';
	foreach($usergroups as $usergroup)
	{
		if($Bambus->UsersAndGroups->isGroup($usergroup) && !$Bambus->UsersAndGroups->isSystemGroup($usergroup))
		{
			$grparray[$usergroup] = htmlentities($usergroup);
			if($Bambus->UsersAndGroups->getPrimaryGroup($victim) == $usergroup)
			{
				$selected = $usergroup;
			}
		}
	}
	foreach(array_keys($grparray) as $grpkey)
	{
		printf('<option value="%s"%s>%s</option>', $grpkey, (($selected == $grpkey) ? ' selected="selected"': ''), $grparray[$grpkey]);
	}
	print('</select></td></tr>');
	echo $Bambus->Gui->endTable();
	echo $Bambus->Gui->verticalSpace();
	
	///////////////////
	//assigned groups//
	///////////////////
	
	echo $Bambus->Gui->beginTable();
	printf('<tr><th class="tdicon">&nbsp;</th><th>%s</th></tr>',$Bambus->Translation->sayThis('assignment_of_system_groups'));
	$id = 1;
	foreach($Bambus->UsersAndGroups->listSystemGroups() as $sysgroup)
	{
		$desc = '<br /><small><i>'.$Bambus->Translation->sayThis('SystemGroupDescription_'.$sysgroup).'</i></small>';
		$flip = ($flip == '1') ? '2' : '1';
		if($victim != BAMBUS_USER && constant('BAMBUS_GRP_'.strtoupper($sysgroup)))
		{
			if($sysgroup != 'Administrator')
			{
				printf('<tr><th class="tdicon"><input id="sysgroup_%s" type="checkbox" name="join_group_%s" %s/></th>'.
							'<td class="flip_%s"><label for="sysgroup_%s"><img src="%s" alt="" /> %s</label></td></tr>'
						,$id
						,md5($sysgroup)
						,(($Bambus->UsersAndGroups->isMemberOf($victim, 'Administrator')) ? 'disabled="disabled" ' : '')
							.(($Bambus->UsersAndGroups->isMemberOf($victim, $sysgroup)) ? 'checked="checked" ' : '')
						,$flip
						,$id
						,$Bambus->Gui->iconPath('system-group', 'system-group', 'mimetype', 'small')
						,htmlentities($sysgroup).$desc
				);
				$id++;
			}
			else
			{
				printf('<tr><th class="tdicon"><input id="sysgroup_admin" type="checkbox" name="join_group_%s" %s onchange="checkothers(this.checked);" /></th><td class="flip_%s"><label for="sysgroup_admin"><img src="%s" alt="" /> %s</label></td></tr>', md5($sysgroup), (($Bambus->UsersAndGroups->isMemberOf($victim, $sysgroup)) ? 'checked="checked" ' : ''), $flip, $Bambus->Gui->iconPath('system-group', 'system-group', 'mimetype', 'small'), htmlentities($sysgroup).$desc);
			}
		}
		else
		{
				printf('<tr><th class="tdicon"><input type="checkbox" disabled="disabled" %s /></th><td class="flip_%s"><img src="%s" alt="" /> %s</td></tr>', (($Bambus->UsersAndGroups->isMemberOf($victim, $sysgroup)) ? 'checked="checked" ' : ''), $flip, $Bambus->Gui->iconPath('system-group', 'system-group', 'mimetype', 'small'),htmlentities($sysgroup).$desc);
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
	echo $Bambus->Gui->tableHeader(array($Bambus->Translation->sayThis('description')));
	echo $Bambus->Gui->beginTableRow();
	echo htmlentities($Bambus->UsersAndGroups->getGroupDescription($victim));
	echo $Bambus->Gui->endTableRow();
	echo $Bambus->Gui->endTable();
	echo $Bambus->Gui->verticalSpace();
	echo $Bambus->Gui->beginTable();
	echo $Bambus->Gui->tableHeader(array($Bambus->Translation->sayThis('assigned_users')));
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

if(BAMBUS_GRP_EDIT)
{
	?>
	<input type="submit" class="submitinput" value="<?php echo $Bambus->Translation->sayThis("save");?>" onmousedown="document.getElementById('scrollposinput').value = document.getElementById('editorianid').scrollTop;"/>
	<?php
}
if(BAMBUS_GRP_EDIT)
{
    echo $Bambus->Gui->endForm();
}
?>