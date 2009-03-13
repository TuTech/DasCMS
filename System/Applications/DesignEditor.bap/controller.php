<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.designeditor
 * @since 2006-10-11
 * @version 1.0
 */
$controller = SApplication::appController();
$function = RURL::get('_action');
//remapping
RSent::alter('edit', RURL::get('edit'));
if(RSent::hasValue('create'))
{
    $function = 'create';
}
if(!empty($function) && method_exists($controller, $function))
{
    call_user_func_array(
        array($controller, $function), 
        array(RSent::data('UTF-8'))
    );
}
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