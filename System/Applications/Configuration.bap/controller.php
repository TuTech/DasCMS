<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.configuration
 * @since 2006-10-16
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