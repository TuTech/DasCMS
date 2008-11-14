<?php
/************************************************
* Bambus CMS 
* Created:     03. Nov 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
if(isset($panel) && $panel->hasWidgets())
{
    echo '<div id="objectInspectorActiveFullBox">';
}
//user creation
$jsCreate = "alert('permission denied');";
if(PAuthorisation::has('org.bambuscms.credentials.user.create'))
{
    $d = new WDialog('dlg_create_user','create_user', WDialog::SUBMIT|WDialog::CANCEL);
    $d->setButtonCaption(WDialog::SUBMIT, 'create');
    $d->askText('new_user_name','','login_name');
    $d->askText('new_user_real_name','','real_name_optional');
    $d->askText('new_user_email','','email_optional');
    $d->askPassword('new_user_password','password');
    $d->askPassword('new_user_password_check','confirm_password');
    $d->remember('action','create_new_user');
    $d->render();
    $jsCreate = WDialog::openCommand('dlg_create_user');
}
echo "\n";
echo new WScript('var action_add_user = function(){'.$jsCreate.'};');

//password changing
$jsCreate = "alert('permission denied');";
if(PAuthorisation::has('org.bambuscms.credentials.user.change'))
{
    $d = new WDialog('dlg_change_password','change_password', WDialog::SUBMIT|WDialog::CANCEL);
    $d->setButtonCaption(WDialog::SUBMIT, 'change');
    $d->remember('action', 'edit_user_data');
    $d->remember('realName', htmlentities($SUsersAndGroups->getRealName($victim)));
    $d->remember('email', htmlentities($SUsersAndGroups->getEmail($victim)));
    $d->remember('company', htmlentities($SUsersAndGroups->getUserAttribute($victim, 'company')));
    if(!PAuthorisation::isInGroup('Administrator'))
    {
        $d->askPassword('change_password_from_old','old_password');
        $d->askPassword('set_new_password','new_password');
    }
    else
    {
        $d->askPassword('new_password','new_password');
    }
    $d->askPassword('confirm_new_password', 'confirm_password');
    $d->render();
    $jsCreate = WDialog::openCommand('dlg_change_password');
}
echo "\n";
echo new WScript('var action_change_password = function(){'.$jsCreate.'};');







$SUsersAndGroups = SUsersAndGroups::alloc()->init();

$intro = new WIntroduction();
$intro->setTitle($victim, false);
$intro->setIcon('mimetype-user');
echo $intro;

if($edit_mode == 'usr')
{
    echo LGui::hiddenInput('action', 'edit_user_data');
    $prof_tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'profile');
	$prof_tbl->addRow(array('attribute', 'value'));
	//what kind of edit do we have? admin|self|others
	$allowEdit = PAuthorisation::has('org.bambuscms.credentials.user.change');
	$row = ($allowEdit)
		? "<input value=\"%s\" name=\"%s\" class=\"%s\" type=\"%s\" />\n"
		: "%s\n";

	$prof_tbl->addRow(array('login_name', htmlentities($victim)));
	$prof_tbl->addRow(array('last_management_login', @date('r',($SUsersAndGroups->getUserAttribute($victim, 'last_management_login')))));
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
	print('<input type="hidden" name="action_2" value="save_editor_permissions" />');
    echo '<br />';
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
            list($name, $description, $icon, $pri) = array_values(LApplication::getBambusApplicationDescription($item.'/Application.xml'));
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
    else
    {
    	echo LGui::verticalSpace();
    	echo LGui::beginTable();
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
    	        $app_name = substr($editor['item'],0,((strlen(DFileSystem::suffix($editor['item']))+1) * -1));
    	        $id = md5($app_name);
    	        $is_admin = $SUsersAndGroups->isMemberOf($victim, 'Administrator');
    	        //printf('%s %s an administrator; ', $victim, ($is_admin) ? 'is' : 'is not');
    	        $checked = ($is_admin || $SUsersAndGroups->hasPermission($victim, $app_name)) ? ' checked="checked"' : '';
    	        $disabled = ($is_admin || ($app_name == BAMBUS_APPLICATION && $victim == PAuthentication::getUserID())) ? ' disabled="disabled"' : '';
    	        	printf(
    	        		$line,
    	        		$flip,
    	        		$checked.$disabled,
    	        		$id,
    	        		$id,
    	        		$id,
    	        		new WIcon($editor['icon'], '', WIcon::MEDIUM, 'app'),
    	        		SLocalization::get($editor['name']),
    	        		SLocalization::get($editor['desc']),
    	        		''
    	        	);
    	    }
    	}
    	echo LGui::endTable();
    	echo LGui::verticalSpace();
    }
    /////////////////////////////EOF EX PERMS
    
    if(PAuthorisation::has('org.bambuscms.credentials.user.change') || PAuthorisation::has('org.bambuscms.credentials.group.change'))
    {
        echo LGui::endForm();
    }
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
if(isset($panel) && $panel->hasWidgets())
{
    echo '</div>';
}
?>