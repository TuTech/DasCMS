<?php
/************************************************
* Bambus CMS 
* Created:     03. Nov 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/

$jsCreate = "alert('permission denied');";
if(PAuthorisation::has('org.bambuscms.credentials.group.create'))
{
    $d = new WDialog('dlg_create_group','create_group', WDialog::SUBMIT|WDialog::CANCEL);
    $d->setButtonCaption(WDialog::SUBMIT, 'create');
    $d->askText('new_group_name','','name');
    $d->askText('new_group_description','','description');
    $d->remember('action','create_new_group');
    $d->render();
    $jsCreate = WDialog::openCommand('dlg_create_group');
}
echo "\n";
echo new WScript('var action_add_group = function(){'.$jsCreate.'};');
if(PAuthorisation::has('org.bambuscms.credentials.user.change') || PAuthorisation::has('org.bambuscms.credentials.group.change'))
{
	echo LGui::beginForm(array('edit' => ($edit_mode == 'usr' ? 'u:' : 'g:').$victim), 'documentform');
}
$SUsersAndGroups = SUsersAndGroups::alloc()->init();


if($edit_mode == 'usr')
{    
    $intro = new WIntroduction();
    $intro->setTitle($victim, false);
    $intro->setIcon('mimetype-user');
    echo $intro;
    //group assignment
	print('<input type="hidden" name="action" value="save_assignment_of_groups" />');
	
    $group_tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'group_memberships');
    $group_tbl->addRow(array(
        'name',
        'description',
        'assigned'
    ));
    $groups = $SUsersAndGroups->listGroups();
    foreach ($groups as $name => $desc) 
    {
        if(!$SUsersAndGroups->isSystemGroup($name))
        {
        	$group_tbl->addRow(array(
        	    sprintf('<label for="group_%d">%s</label>',  md5($name), htmlentities($name)),
        	    htmlentities($desc),
        	    sprintf('<input id="group_%d" type="checkbox" name="join_group_%s" %s/>',  md5($name), md5($name), (($SUsersAndGroups->isMemberOf($victim, $name)) ? 'checked="checked" ' : '')),
        	));
        }
    }
    $group_tbl->render();
    echo LGui::verticalSpace();
    
    $pri_tbl = new WTable(WTable::HEADING_NONE, 'primary_group');
	$usergroups = $SUsersAndGroups->listGroupsOfUser($victim);
	$dat = sprintf('<select class="selectinput" name="primary_group">');
	$grparray = array('' => SLocalization::get('none'));
	$selected = '';
	foreach($usergroups as $usergroup)
	{
		if($SUsersAndGroups->isGroup($usergroup) && !$SUsersAndGroups->isSystemGroup($usergroup))
		{
			$grparray[$usergroup] = htmlentities($usergroup);
			if($SUsersAndGroups->getPrimaryGroup($victim) == $usergroup)
			{
				$selected = $usergroup;
			}
		}
	}
	foreach(array_keys($grparray) as $grpkey)
	{
		$dat .= sprintf('<option value="%s"%s>%s</option>', $grpkey, (($selected == $grpkey) ? ' selected="selected"': ''), $grparray[$grpkey]);
	}
	$dat .= '</select>';
	$pri_tbl->addRow(array(
        $dat
    ));
    $pri_tbl->render();
	echo LGui::verticalSpace();
	///////////////////
	//assigned groups//
	///////////////////
	
	$perm_tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'permissions');
	$perm_tbl->addRow(array(
        'name',
        'description',
        'assigned'
    ));
	foreach($SUsersAndGroups->listSystemGroups() as $sysgroup)
	{
		$desc = SLocalization::get('SystemGroupDescription_'.$sysgroup);
		if($victim != PAuthentication::getUserID() && PAuthorisation::has('org.bambuscms.credentials.group.change'))
		{
		    //editable
		    $label = sprintf(
		        '<label for="sysgroup_%s">%s</label>',
		        md5($sysgroup),
		        $sysgroup
		    );
		    $checkbox = sprintf(
		        '<input id="sysgroup_%s" type="checkbox" name="join_group_%s" %s%s/>',
		        md5($sysgroup),
		        md5($sysgroup),
		        (($SUsersAndGroups->isMemberOf($victim, $sysgroup)) ? 'checked="checked" ' : ''),
		        ($sysgroup == 'Administrator') ?  ' onchange="checkothers(this.checked);"' : ''
		    );
	        $perm_tbl->addRow(array(
	            $label,
	            $desc,
	            $checkbox
	        ));
		}
		else
		{
				$perm_tbl->addRow(array(
				    $sysgroup,
				    $desc,
				    '<input type="checkbox" disabled="disabled" '.(($SUsersAndGroups->isMemberOf($victim, $sysgroup)) ? 'checked="checked" ' : '').' />'
				));
		}
		
	}
	$perm_tbl->render();
}
else
{
/////////////////////
//group information// 
/////////////////////

    $intro = new WIntroduction();
    $intro->setTitle($victim, false);
    $intro->setIcon('mimetype-group');
    $intro->setDescription(htmlentities($SUsersAndGroups->getGroupDescription($victim)), false);
    echo $intro;
	
    $members = new WFlowLayout('members');
	$assignedUsers = $SUsersAndGroups->listUsersOfGroup($victim);
	if(is_array($assignedUsers) && count($assignedUsers) > 0)
	{
	    sort($assignedUsers, SORT_LOCALE_STRING);
		foreach($assignedUsers as $user)
		{
			$members->addItem(sprintf(
					'<div><b>%s</b><br />%s</div>'
					,htmlentities($user)
					,($SUsersAndGroups->isMemberOf($user, 'Administrator')) ? 'Administrator' : 'User'
				));
		}
	}	
	else
	{
	    $members->addItem('-');
	} 
	
	$members->render();
	
}

if(PAuthorisation::has('org.bambuscms.credentials.user.change') || PAuthorisation::has('org.bambuscms.credentials.group.change'))
{
	?>
	<input type="submit" class="submitinput" value="<?php SLocalization::out("save");?>" onmousedown="document.getElementById('scrollposinput').value = document.getElementById('editorianid').scrollTop;"/>
	<?php
    echo LGui::endForm();
}
?>