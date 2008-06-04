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
echo '<div id="objectInspectorActiveFullBox">';
//editing allowed?
	
if(BAMBUS_GRP_EDIT && isset($Page) && $Page instanceof CPage)
{
		
	printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2>'
		, htmlentities($Page->Title, ENT_QUOTES, 'UTF-8')
		, htmlentities($Page->Title, ENT_QUOTES, 'UTF-8')
		);
	//////////////
	//css editor//
	//////////////
	echo $Bambus->Gui->beginEditorWrapper();
	echo $Bambus->Gui->editorTextarea($Page->Content);
	echo $Bambus->Gui->endEditorWrapper();
	echo $Bambus->Gui->beginScript();
	echo 'initeditor();';
	echo $Bambus->Gui->endScript();
}
else
{
	echo $Bambus->Gui->script('BCMSRunFX[BCMSRunFX.length] = function(){OBJ_ofd.show()};');
}
echo '</form></div>';
?>