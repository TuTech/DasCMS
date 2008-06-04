<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
//document title

//editing allowed?
if(BAMBUS_GRP_EDIT && $FileOpened)
{
	printf('<h2>%s: %s</h2>'
		,$Bambus->Translation->treturn((BAMBUS_APPLICATION_TAB == 'edit_templates') ? 'template' : 'stylesheet')
		, htmlspecialchars($FileName, ENT_QUOTES, 'utf-8'));
		
	if(BAMBUS_GRP_RENAME && !in_array($File,array('default.css','header.tpl','footer.tpl','body.tpl')))
	{
		printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/>', htmlentities($FileName));
	}
	//////////////
	//css editor//
	//////////////
	
	echo $Bambus->Gui->beginEditorWrapper();
	echo $Bambus->Gui->editorTextarea(utf8_encode($fileContent));
	echo $Bambus->Gui->endEditorWrapper();
	echo $Bambus->Gui->beginScript();
	echo 'initeditor();';
	echo $Bambus->Gui->endScript();
}
elseif(!$FileOpened)
{
	echo $Bambus->Gui->script('BCMSRunFX[BCMSRunFX.length] = function(){OBJ_ofd.show()};');
}
	echo '</form>';
?>