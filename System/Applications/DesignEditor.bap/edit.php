<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
//document title
if(isset($panel) && $panel->hasWidgets())
{
    echo '<div id="objectInspectorActiveFullBox">';
}
//editing allowed?
if($FileOpened)
{
	printf('<h2>%s: %s</h2>'
		,SLocalization::get((BAMBUS_APPLICATION_TAB == 'edit_templates') ? 'template' : 'stylesheet')
		,htmlspecialchars($FileName, ENT_QUOTES, 'utf-8'));
		
	if((((BAMBUS_APPLICATION_TAB == 'edit_templates') &&  PAuthorisation::has('org.bambuscms.layout.template.create') && PAuthorisation::has('org.bambuscms.layout.template.delete')) ||
        ((BAMBUS_APPLICATION_TAB != 'edit_templates') && PAuthorisation::has('org.bambuscms.layout.stylesheet.create') && PAuthorisation::has('org.bambuscms.layout.stylesheet.delete')))
	&& !in_array($File,array('default.css','header.tpl','footer.tpl','body.tpl')))
	{
		printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/>', htmlentities($FileName));
	}
	//////////////
	//css editor//
	//////////////
	
	echo LGui::beginEditorWrapper();
	echo LGui::editorTextarea($fileContent);
	echo LGui::endEditorWrapper();
	echo new WScript('org.bambuscms.wcodeeditor.run(document.getElementById("editorianid"));');
}
if(isset($panel) && $panel->hasWidgets())
{
    echo '</div>';
}
echo '</form>';
?>