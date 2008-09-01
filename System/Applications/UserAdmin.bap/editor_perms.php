<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
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
	printf("<tr valign=\"top\"><td class=\"addWrapper\"><a id=\"addUserLink\" class=\"activeAddButton\" href=\"javascript:addUser()\"><img src=\"%s\" alt=\"\" /></a><br /><a id=\"addGroupLink\" class=\"inactiveAddButton\" href=\"javascript:addGroup()\"><img src=\"%s\" alt=\"\" /></a></td><td>", WIcon::pathFor('user', 'mimetype', WIcon::MEDIUM), WIcon::pathFor('group', 'mimetype', WIcon::MEDIUM));
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
/////////////////////
//editorpermissions//
/////////////////////

if(BAMBUS_GRP_EDIT)
{
	printf('<form method="post" onchange="showPreview()" id="documentform" name="documentform" action="%s">',$Bambus->Linker->createQueryString());
	if($edit_mode == 'usr')
	{
    	echo $Bambus->Gui->hiddenInput('action', 'save_editor_permissions');
	}
	else
	{
		echo $Bambus->Gui->hiddenInput('action', 'save_editor_group_permissions');
	}
}
	printf('<h2>%s: %s</h2>'
		,SLocalization::get(($edit_mode == 'usr') ? 'user' : 'group')
		, htmlspecialchars($victim, ENT_QUOTES, 'utf-8'));

$myDir = getcwd();//nice place... remember it
chdir($Bambus->pathTo('systemApplication'));
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
chdir(constant('BAMBUS_CMS_ROOTDIR'));
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
	        		new WIcon($editor['icon'], '', null, 'app'),
	        		SLocalization::get($editor['name']),
	        		SLocalization::get($editor['desc']),
	        		''
	        	);
	    }
	}
	echo $Bambus->Gui->endTable();
	echo $Bambus->Gui->verticalSpace();
}
if(BAMBUS_GRP_EDIT)
{
	printf('<input type="submit" class="submitinput" onmousedown="commitChanges()" value="%s" />', SLocalization::get("save"));
    echo $Bambus->Gui->endForm();
}
?>