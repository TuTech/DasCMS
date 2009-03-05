<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.protectiontags
 * @since 2008-11-12
 * @version 1.0
 */
if(RSent::has('content') && PAuthorisation::has('org.bambuscms.system.permissions.tags.change'))
{
    $tags = STag::parseTagStr(RSent::get('content', 'utf-8'));
    STagPermissions::setProtectedTags($tags);
    SNotificationCenter::report('message', 'tags_set');
}
try
{
	$panel = WSidePanel::alloc()->init();
	$panel->setMode(
	    WSidePanel::HELPER |
	    WSidePanel::INFORMATION
    );
	//echo $panel;
}
catch(Exception $e){
	echo $e->getTraceAsString();
}
?>