<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.templateeditor
 * @since 2006-10-11
 * @version 1.0
 */
$AppController = BAppController::getControllerForID('org.bambuscms.applications.textbricks');

$editExist = (RURL::has('edit')) && CTextBrick::Exists(RURL::get('edit','utf-8'));
$Brick = null;
//delete
if($editExist && RSent::get('delete','utf-8') != '' && PAuthorisation::has('org.bambuscms.content.ctextbricks.delete'))
{
	CTextBrick::Delete(RURL::get('edit','utf-8'));
	$editExist = false;
}
if(RSent::get('action') == 'delete' && PAuthorisation::has('org.bambuscms.content.ctextbricks.delete'))
{
	foreach (RSent::data('utf-8') as $k => $v) 
	{
		if(substr($k,0,7) == 'select_' && !empty($v))
		{
			//delete
			CTextBrick::Delete(substr($k,7));
		}
	}
}

//create
elseif(RSent::hasValue('create') && PAuthorisation::has('org.bambuscms.content.ctextbricks.create'))
{
	$Title = RSent::get('create','utf-8');
	$Brick = CTextBrick::Create($Title);
}

//open for editing
elseif($editExist && PAuthorisation::has('org.bambuscms.content.ctextbricks.change'))
{
	$Brick = CTextBrick::Open(RURL::get('edit','utf-8'));
}

//save data
if(isset($Brick) && $Brick instanceof CTextBrick && PAuthorisation::has('org.bambuscms.content.ctextbricks.change'))
{
	if(RSent::has('content'))
	{
		$Brick->RAWContent = RSent::get('content','utf-8');
	}
	if(RSent::has('filename'))
	{
		$Brick->Title = RSent::get('filename','utf-8');
	}
}
echo new WOpenDialog($AppController, $Brick);


printf(
    '<form method="post" id="documentform" name="documentform" action="%s">'
	,SLink::link(array('edit' => (isset($Brick) && $Brick instanceof CTextBrick)? $Brick->Alias :''))
);

$panel = WSidePanel::alloc()->init();
$panel->setMode(
    WSidePanel::MEDIA_LOOKUP|
    WSidePanel::CONTENT_LOOKUP|
    WSidePanel::PROPERTY_EDIT|
    WSidePanel::HELPER|
    WSidePanel::PERMISSIONS);
if(isset($Brick))
{
    $panel->setTargetContent($Brick);
}
$panel->processInputs();
if(isset($Brick) && $Brick instanceof CTextBrick && $Brick->isModified())
{
	$Brick->Save();
}

?>