<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.templateeditor
 * @since 2006-10-11
 * @version 1.0
 */
$AppController = BAppController::getControllerForID('org.bambuscms.applications.templates');

$allowEdit = true;
$FileOpened = false;

$editExist = (RURL::has('edit')) && CTemplate::Exists(RURL::get('edit','utf-8'));
$Tpl = null;
//delete
if($editExist && RSent::get('delete','utf-8') != '' && PAuthorisation::has('org.bambuscms.content.ctemplate.delete'))
{
	CTemplate::Delete(RURL::get('edit','utf-8'));
	$editExist = false;
}
//create
elseif(RSent::hasValue('create') && PAuthorisation::has('org.bambuscms.content.ctemplate.create'))
{
	$Title = RSent::get('create','utf-8');
	$Tpl = CTemplate::Create($Title);
}

//open for editing
elseif($editExist && PAuthorisation::has('org.bambuscms.content.ctemplate.change'))
{
	$Tpl = CTemplate::Open(RURL::get('edit','utf-8'));
}

//save data
if(isset($Tpl) && $Tpl instanceof CTemplate && PAuthorisation::has('org.bambuscms.content.ctemplate.change'))
{
	if(RSent::has('content'))
	{
		$Tpl->RAWContent = RSent::get('content','utf-8');
	}
	if(RSent::has('filename'))
	{
		$Tpl->Title = RSent::get('filename','utf-8');
	}
}
echo new WOpenDialog($AppController, $Tpl);

WTemplate::globalSet('DocumentFormAction', SLink::link(array('edit' => (isset($Tpl) && $Tpl instanceof CTemplate)? $Tpl->Alias :'')));
$ex = '';

try{
	$panel = WSidePanel::alloc()->init();
	$panel->setMode(
	    WSidePanel::MEDIA_LOOKUP|
	    WSidePanel::CONTENT_LOOKUP|
	    WSidePanel::PROPERTY_EDIT|
	    WSidePanel::HELPER|
	    WSidePanel::PERMISSIONS);
    if(isset($Tpl))
    {
        $panel->setTargetContent($Tpl);
    }
    $panel->processInputs();
    if(isset($Tpl) && $Tpl instanceof CTemplate && $Tpl->isModified())
    {
    	$Tpl->Save();
    }
}
catch(Exception $e){
    $ex = '<div class="TemplateError"><h4>%s</h4><p><b>%s thrown in %s at line %d</b></p><p%s<br /><code>%s</code></p></div>';
    $ex = sprintf($ex, $e->getMessage(), get_class($e), $e->getFile(), $e->getLine(), $e->getMessage(), $e->getTraceAsString());
	SNotificationCenter::report('warning', 'invalid_template_not_executeable');
}	

?>