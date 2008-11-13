<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
$AppController = BAppController::getControllerForID('org.bambuscms.applications.templates');

$allowEdit = true;
$FileOpened = false;

$editExist = (RURL::has('edit')) && CTemplate::Exists(RURL::get('edit'));
$Tpl = null;
//delete
if($editExist && RSent::get('delete') != '' && PAuthorisation::has('org.bambuscms.content.ctemplate.delete'))
{
	CTemplate::Delete(RURL::get('edit'));
	$editExist = false;
}
if(RSent::get('action') == 'delete' && PAuthorisation::has('org.bambuscms.content.ctemplate.delete'))
{
	foreach (RSent::data() as $k => $v) 
	{
		if(substr($k,0,7) == 'select_' && !empty($v))
		{
			//delete
			CTemplate::Delete(substr($k,7));
		}
	}
}

//create
elseif(RSent::hasValue('create') && PAuthorisation::has('org.bambuscms.content.ctemplate.create'))
{
	$Title = RSent::get('create');
	$Tpl = CTemplate::Create($Title);
}

//open for editing
elseif($editExist && PAuthorisation::has('org.bambuscms.content.ctemplate.change'))
{
	$Tpl = CTemplate::Open(RURL::get('edit'));
}

//save data
if(isset($Tpl) && $Tpl instanceof CTemplate && PAuthorisation::has('org.bambuscms.content.ctemplate.change'))
{
	if(RSent::has('content'))
	{
		$Tpl->RAWContent = RSent::get('content');
	}
	if(RSent::has('filename'))
	{
		$Tpl->Title = RSent::get('filename');
	}
	
}
echo new WOpenDialog($AppController, $Tpl);


printf(
    '<form method="post" id="documentform" name="documentform" action="%s">'
	,SLink::link(array('edit' => (isset($Tpl) && $Tpl instanceof CTemplate)? $Tpl->Alias :''))
);
if(isset($Tpl))
{
	try{
		$panel = new WSidePanel();
		$panel->setMode(
		    WSidePanel::MEDIA_LOOKUP|
		    WSidePanel::CONTENT_LOOKUP|
		    WSidePanel::PROPERTY_EDIT|
		    WSidePanel::HELPER|
		    WSidePanel::PERMISSIONS);
		$panel->setTargetContent($Tpl);
		echo $panel;
		if($Tpl instanceof CTemplate && $Tpl->isModified())
		{
			$Tpl->Save();
		}
	}
	catch(Exception $e){
	    echo "\n\n";
	    echo $e->getMessage();
		echo $e->getTraceAsString();
		SNotificationCenter::report('warning', 'invalid_template_not_executeable');
	}	
}
?>