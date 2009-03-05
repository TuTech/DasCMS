<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2006-10-11
 * @version 1.0
 */
if(isset($Page) && $Page instanceof CPage)
{
    printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2>'
    	, htmlentities($Page->Title, ENT_QUOTES, 'UTF-8')
    	, htmlentities($Page->Title, ENT_QUOTES, 'UTF-8')
    	);
    echo LGui::editorTextarea($Page->Content, true, LConfiguration::get('use_wysiwyg') != '');
    if(!LConfiguration::get('use_wysiwyg'))
    {
        echo new WScript('org.bambuscms.wcodeeditor.run($(org.bambuscms.app.document.editorElementId));');
    }
}
echo LGui::endForm();
?>