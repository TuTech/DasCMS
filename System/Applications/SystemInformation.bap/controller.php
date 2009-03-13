<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.systeminformation
 * @since 2006-10-16
 * @version 1.0
 */
$controller = SApplication::appController();
$function = RURL::get('_action');
if(!empty($function) && method_exists($controller, $function))
{
    call_user_func_array(
        array($controller, $function), 
        array(RSent::data('UTF-8'))
    );
}
?>