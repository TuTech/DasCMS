<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2009-04-28
 * @version 1.0
 */
$Search = SApplication::getControllerContent();
if($Search instanceof CSearch)
{
    echo new WContentTitle($Search);
}
?>