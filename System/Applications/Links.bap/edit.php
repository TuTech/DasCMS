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
    echo new WContentTitle($Link);
    printf('<h3>%s</h3><input type="text" id="content_input" name="content" value="%s"/>'
    	, SLocalization::get('url')
        , htmlentities($Link->Content, ENT_QUOTES, 'UTF-8')
	);
}
?>