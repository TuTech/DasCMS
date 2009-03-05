<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
//editing allowed?
echo $ex;	

if(PAuthorisation::has('org.bambuscms.content.ctemplate.change') && isset($Tpl) && $Tpl instanceof CTemplate )
{
		
	printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2>'
		, htmlentities($Tpl->Title, ENT_QUOTES, 'UTF-8')
		, htmlentities($Tpl->Title, ENT_QUOTES, 'UTF-8')
		);
	echo LGui::editorTextarea($Tpl->RAWContent);
	echo new WScript('org.bambuscms.wcodeeditor.run($(org.bambuscms.app.document.editorElementId));');
}
echo LGui::endForm();
?>