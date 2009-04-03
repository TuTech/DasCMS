<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2006-10-11
 * @version 1.0
 */
$Link = SApplication::getControllerContent();
if(isset($Link) && $Link instanceof CLink)
{
    printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2>'.
            '<input type="text" id="content_input" name="content" value="%s"/>'
    	, htmlentities($Link->Title, ENT_QUOTES, 'UTF-8')
    	, htmlentities($Link->Title, ENT_QUOTES, 'UTF-8')
    	, htmlentities($Link->Content, ENT_QUOTES, 'UTF-8')
	);
}
?>