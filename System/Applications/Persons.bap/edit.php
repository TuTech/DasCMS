<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.templateeditor
 * @since 2006-10-11
 * @version 1.0
 */
$User = SApplication::getControllerContent();
if(isset($User) && $User instanceof CPerson)
{
    printf('<input type="hidden" id="alias" value="%s" /><h2>%s</h2><input type="hidden" id="CP_UID" value="%s" />'
    	, htmlentities($User->Alias, ENT_QUOTES, 'UTF-8')
    	, htmlentities($User->Title, ENT_QUOTES, 'UTF-8')
    	, $User->hasLogin() ? htmlentities($User->getLoginName(), ENT_QUOTES, 'UTF-8') : ''
    	);
    $inactive = !$User->hasLogin() 
        ? array('R') //user does not have login so remove Revoke action
        : ($User->getLoginName() == PAuthentication::getUserID()
                ? array('I', 'R')//this user has a login so it can't be created and he shod not remove it either
                : array('I')//this user has a login, too remove login create action
          );
    echo '<script type="text/javascript">';
    foreach($inactive as $i)
    {
        printf(
            	'$("CommandBarPanel_credential_actions").removeChild($("App-Hotkey-CTRL-%s"));'.
                'org.bambuscms.app.hotkeys.unregister("App-Hotkey-CTRL-%s");'
            ,$i
            ,$i
        );
     }
     echo $User->getLoginName() == PAuthentication::getUserID() 
            ? '$("CommandBarPanel_credential_actions").style.display="none"':'';
     echo '</script>';
}
?>
<div id="org_bambuscms_app_persons_gui">

</div>
