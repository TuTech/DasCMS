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
    
    printf('<input type="hidden" id="alias" value="%s" /><h2>%s</h2>'
    	, htmlentities($User->Alias, ENT_QUOTES, 'UTF-8')
    	, htmlentities($User->Title, ENT_QUOTES, 'UTF-8')
    	);
}
?>
<div id="org_bambuscms_app_persons_gui">

</div>
