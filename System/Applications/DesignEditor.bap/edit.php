<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.designeditor
 * @since 2006-10-11
 * @version 1.0
 */
if($FileOpened)
{
	printf('<h2>%s</h2>', htmlspecialchars($FileName, ENT_QUOTES, 'utf-8'));
	printf('<input type="hidden" id="filename" name="filename" value="%s"/>', htmlentities($FileName));
	echo LGui::editorTextarea($fileContent);
	echo new WScript('org.bambuscms.wcodeeditor.run($(org.bambuscms.app.document.editorElementId));');
}
echo '</form>';
?>