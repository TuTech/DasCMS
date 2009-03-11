<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.templateeditor
 * @since 2006-10-11
 * @version 1.0
 */
if(isset($Brick) && $Brick instanceof CTextBrick)
{
    printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2>'
    	, htmlentities($Brick->Title, ENT_QUOTES, 'UTF-8')
    	, htmlentities($Brick->Title, ENT_QUOTES, 'UTF-8')
    	);
    echo LGui::editorTextarea($Brick->RAWContent);
    echo new WScript('org.bambuscms.wcodeeditor.run($(org.bambuscms.app.document.editorElementId));');
    echo LGui::endForm();
}
?>