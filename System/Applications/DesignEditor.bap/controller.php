<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.designeditor
 * @since 2006-10-11
 * @version 1.0
 */
//remapping
RSent::alter('edit', RURL::get('edit'));

BAppController::callController(
    SApplication::appController(), 
    RURL::get('_action'), 
    RSent::data('UTF-8')
);

$controller = SApplication::appController();
if($controller->getChangedContent() !== null)
{
    RSent::alter('edit', $controller->getChangedContent());
}
$currentFile = '';
if(RSent::hasValue('edit'))
{
    $file = basename(RSent::get('edit'));
    if(file_exists(SPath::DESIGN.$file) || $controller->hasCreated())
    {
        $panel = WSidePanel::alloc()->init();
        $panel->setTarget($file, 'text/css');
        $currentFile = $file;
        WTemplate::globalSet(
        	'DocumentFormAction',  
            SLink::link(array('edit' => $currentFile, '_action' => 'save'))
        );
    }
}

echo new WOpenDialog($controller, $currentFile);

?>