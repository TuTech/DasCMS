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
	printf(
		'<input type="hidden" id="filename" name="filename" value="%s"/><h2>%s</h2>'
	    ,htmlentities($FileName, ENT_QUOTES, 'utf-8')
	    ,htmlentities($FileName, ENT_QUOTES, 'utf-8')
    );
	$editor = new WTextEditor($fileContent);
    $editor->disableSpellcheck();
    echo $editor;
}
echo '</form>';
?>