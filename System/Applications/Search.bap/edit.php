<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2006-10-11
 * @version 1.0
 */


$Search = SApplication::getControllerContent();
if($Search instanceof CSearch)
{
    echo new View_UIElement_ContentTitle($Search);
}
?>