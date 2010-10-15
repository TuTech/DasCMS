<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.useradmin
 * @since 2006-11-03
 * @version 1.0
 */
$jsCreate = "alert('permission denied');";
if(PAuthorisation::has('org.bambuscms.credentials.user.create'))
{
    $d = new View_UIElement_Dialog('dlg_create_user','create_user', View_UIElement_Dialog::SUBMIT|View_UIElement_Dialog::CANCEL);
    $d->setButtonCaption(View_UIElement_Dialog::SUBMIT, 'create');
    $d->askText('new_user_name','','login_name');
    $d->askText('new_user_real_name','','real_name_optional');
    $d->askText('new_user_email','','email_optional');
    $d->askPassword('new_user_password','password');
    $d->askPassword('new_user_password_check','confirm_password');
    $d->remember('action','create_new_user');
    $d->render();
    $jsCreate = View_UIElement_Dialog::openCommand('dlg_create_user');
}
echo "\n";
echo new View_UIElement_Script('var action_add_user = function(){'.$jsCreate.'};');

//password changing
$jsCreate = "alert('permission denied');";
if(PAuthorisation::has('org.bambuscms.credentials.user.change'))
{
    $d = new View_UIElement_Dialog('dlg_change_password','change_password', View_UIElement_Dialog::SUBMIT|View_UIElement_Dialog::CANCEL);
    $d->setButtonCaption(View_UIElement_Dialog::SUBMIT, 'change');
    $d->remember('action', 'edit_user_data');
    $d->remember('realName', htmlentities($SUsersAndGroups->getRealName($victim)));
    $d->remember('email', htmlentities($SUsersAndGroups->getEmail($victim)));
    $d->remember('company', htmlentities($SUsersAndGroups->getUserAttribute($victim, 'company')));
    if(!PAuthorisation::isInGroup('Administrator'))
    {
        $d->askPassword('change_password_from_old','old_password');
        $d->askPassword('set_new_password','new_password');
        $d->askPassword('confirm_new_password', 'confirm_password');
    }
    else
    {//admin 
        $d->askPassword('adm_set_password','new_password');
        $d->askPassword('adm_set_password_confirm','new_password');
    }
    $d->render();
    $jsCreate = View_UIElement_Dialog::openCommand('dlg_change_password');
}
echo "\n";
echo new View_UIElement_Script('var action_change_password = function(){'.$jsCreate.'};');

$SUsersAndGroups = SUsersAndGroups::getInstance();

$intro = new View_UIElement_Introduction();
$intro->setTitle(mb_convert_encoding($victim, CHARSET, 'iso-8859-1'), false);
$intro->setIcon('mimetype-user');
echo $intro;

if($edit_mode == 'usr')
{
    echo '<input type="hidden" name="action" value="edit_user_data" />';
    $prof_tbl = new View_UIElement_Table(View_UIElement_Table::HEADING_TOP|View_UIElement_Table::HEADING_LEFT, 'profile');
	$prof_tbl->addRow(array('attribute', 'value'));
	//what kind of edit do we have? admin|self|others
	$allowEdit = PAuthorisation::has('org.bambuscms.credentials.user.change');
	$row = ($allowEdit)
		? "<input value=\"%s\" name=\"%s\" class=\"%s\" type=\"%s\" />\n"
		: "%s\n";

	$prof_tbl->addRow(array('login_name', htmlentities($victim)));
	$dat = intval($SUsersAndGroups->getUserAttribute($victim, 'last_management_login')); 
	if(!empty($dat))
	{
	    $prof_tbl->addRow(array('last_management_login', date('r',($dat))));
	}
	$prof_tbl->addRow(array('management_login_count', htmlentities($SUsersAndGroups->getUserAttribute($victim, 'management_login_count'))));
	$prof_tbl->addRow(array(
	    'real_name', 
	    sprintf(
	        $row
	        ,htmlentities($SUsersAndGroups->getRealName($victim))
	        ,'realName'
			,'fullinput'
			,'text'
	    )));
	$prof_tbl->addRow(array(
	    'email', 
	    sprintf(
	        $row
	        ,htmlentities($SUsersAndGroups->getEmail($victim))
	        ,'email'
			,'fullinput'
			,'text'
	    )));
	$prof_tbl->addRow(array(
	    'company', 
	    sprintf(
	        $row
	        ,htmlentities($SUsersAndGroups->getUserAttribute($victim, 'company'))
	        ,'att_company'
			,'fullinput'
			,'text'
	    )));
    $prof_tbl->render();
	

    
    /////////////////////////////EX PERMS
	print('<input type="hidden" name="action_2" value="save_editor_permissions" /><br />');
    $myDir = getcwd();//nice place... remember it
    chdir(SPath::SYSTEM_APPLICATIONS);
    $Dir = opendir ('./'); 
    $items = array();
    $i = 0;
    while ($item = readdir ($Dir)) 
    {
    	if(is_dir($item) && substr($item,0,1) != '.' && strtolower(DFileSystem::suffix($item)) == 'bap')
        {
        	//BambusApplicartion-Package
            $i++;
            list($name, $description, $icon, $guid) = array_values(SBapReader::getAttributesOf($item, array('name', 'description', 'icon', 'guid')));
            $available[$i] = array('item' => $item,'name' => $name,'desc' => $description,'icon' => $icon, 'type' => 'application', 'guid' => $guid);
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
	echo "<br />";
	echo '<table cellspacing="0" class="borderedtable full">';
	printf('<tr><th></th><th colspan="2">%s</th></tr>', SLocalization::get('editor_permissions'));
	$line = <<<EOX
			<tr class="flip_%s">
				<th style="width:18px;">
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
	
	foreach($available as $editor)
	{
	    $flip = ($flip == '1') ? '2' : '1';
	    if(!empty($editor))
	    {
	        $app_name = $editor['guid'];
	        $id = md5($app_name);
	        $is_admin = $SUsersAndGroups->isMemberOf($victim, 'Administrator');
	        //printf('%s %s an administrator; ', $victim, ($is_admin) ? 'is' : 'is not');
	        $checked = ($is_admin || $SUsersAndGroups->hasPermission($victim, $app_name)) ? ' checked="checked"' : '';
	        $disabled = ($is_admin || ($app_name == SApplication::getInstance()->getName() && $victim == PAuthentication::getUserID())) ? ' disabled="disabled"' : '';
	        	printf(
	        		$line,
	        		$flip,
	        		$checked.$disabled,
	        		$id,
	        		$id,
	        		$id,
	        		new View_UIElement_Icon($editor['icon'], '', View_UIElement_Icon::MEDIUM, 'app'),
	        		SLocalization::get($editor['name']),
	        		SLocalization::get($editor['desc']),
	        		''
	        	);
	    }
	}
	echo '</table>';
	echo "<br />";
    /////////////////////////////EOF EX PERMS
    
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
	echo '<h3>',SLocalization::get('description'),'</h3><p>',htmlentities($SUsersAndGroups->getGroupDescription($victim)),'</p>';
	echo '<h3>',SLocalization::get('assigned_users'),'</h3>';
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
	
}
?>