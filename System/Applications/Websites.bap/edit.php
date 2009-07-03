<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.websites
 * @since 2006-10-11
 * @version 1.0
 */
$Page = SApplication::getControllerContent();
if(isset($Page) && $Page instanceof CPage)
{
    echo new WContentTitle($Page);
    $editor = new WTextEditor($Page->Content);
    $editor->setWYSIWYG(LConfiguration::get('use_wysiwyg') != '');
    echo $editor;
}
?>