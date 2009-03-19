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
    printf('<h2>%s</h2>'
    	, htmlentities($User->Title, ENT_QUOTES, 'UTF-8')
    	);
}
?>
<h3>Tel.:</h3>
<div class="PersonAttributeBlock">
	<select class="PersonContextSelect">
		<optgroup label="Contexts">
			<option>Arbeit</option>
			<option>Privat</option>
		</optgroup>
		<optgroup label="Aktionen">
			<option>Eigenschaft entfernen</option>
			<option>Neuer Context</option>
			<option>Ungenutzte Contexte entfernen</option>
		</optgroup>
	</select>
	<input type="text" value="+49 15464 35 345 32">
	<br />
	<br />
	<select class="">
		<option>Eigenschaft hinzufügen</option>
		<optgroup label="Contexts">
			<option>Arbeit</option>
			<option>Privat</option>
		</optgroup>
	</select>
</div>
<div id="org_bambuscms_app_persons_gui">

</div>
