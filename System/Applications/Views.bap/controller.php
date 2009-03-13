<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.views
 * @since 2007-11-28
 * @version 1.0
 */
BAppController::callController(
    SApplication::appController(), 
    RURL::get('_action'), 
    RSent::data('UTF-8')
);
WTemplate::globalSet(
	'DocumentFormAction' 
    ,SLink::link(array('_action' => 'save'))
);
?>
