<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css editor interface
************************************************/
//editing allowed?
if($FileOpened)
{
	printf('<h2>%s: %s</h2>'
		,SLocalization::get('stylesheet')
		,htmlspecialchars($FileName, ENT_QUOTES, 'utf-8'));
		
	if((PAuthorisation::has('org.bambuscms.layout.stylesheet.create') 
	    && PAuthorisation::has('org.bambuscms.layout.stylesheet.delete'))
	    && $File != 'default.css')
	{
		printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/>', htmlentities($FileName));
	}
	echo LGui::editorTextarea($fileContent);
	echo new WScript('org.bambuscms.wcodeeditor.run($(org.bambuscms.app.document.editorElementId));');
}
echo '</form>';
?>