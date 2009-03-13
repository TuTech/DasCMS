<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.systeminformation
 * @since 2006-10-16
 * @version 1.0
 */
BAppController::callController(
    SApplication::appController(), 
    RURL::get('_action'), 
    RSent::data('UTF-8')
);
?>