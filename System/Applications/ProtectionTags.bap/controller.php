<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.protectiontags
 * @since 2008-11-12
 * @version 1.0
 */
$App = SApplication::alloc()->init();
$controller = BAppController::getControllerForID($App->getGUID());
$function = RURL::get('_action');
if(!empty($function) && method_exists($controller, $function))
{
    call_user_func_array(
        array($controller, $function), 
        array(RSent::data('UTF-8'))
    );
}
WTemplate::globalSet(
	'DocumentFormAction' 
    ,SLink::link(array('_action' => 'save'))
);
?>