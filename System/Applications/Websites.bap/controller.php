<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
$AppController = BAppController::getControllerForID('org.bambuscms.applications.websiteeditor');

$allowEdit = true;
$FileOpened = false;
$SCI = SContentIndex::alloc()->init();

$editExist = (RURL::has('edit')) && CPage::Exists(RURL::get('edit'));
$Page = null;
//delete
if($editExist && RSent::get('delete', 'utf-8') != '' && PAuthorisation::has('org.bambuscms.content.cpage.delete'))
{
	CPage::Delete(RURL::get('edit', 'utf-8'));
	$editExist = false;
}
if(RSent::get('action') == 'delete' && PAuthorisation::has('org.bambuscms.content.cpage.delete'))
{
	foreach (RSent::data('utf-8') as $k => $v) 
	{
		if(substr($k,0,7) == 'select_' && !empty($v))
		{
			//delete
			CPage::Delete(substr($k,7));
		}
	}
}

//create
elseif(RSent::hasValue('create') && PAuthorisation::has('org.bambuscms.content.cpage.create'))
{
	$Title = RSent::get('create', 'utf-8');
	$Page = CPage::Create($Title);
	$Page->Content = $Title;
}

//open for editing
elseif($editExist && PAuthorisation::has('org.bambuscms.content.cpage.change'))
{
	$Page = CPage::Open(RURL::get('edit', 'utf-8'));
}

//save data
if(isset($Page) && $Page instanceof CPage && PAuthorisation::has('org.bambuscms.content.cpage.change'))
{
	if(RSent::has('content'))
	{
		$Page->Content = RSent::get('content', 'utf-8');
	}
	if(RSent::has('filename'))
	{
		$Page->Title = RSent::get('filename', 'utf-8');
	}
	
}
echo new WOpenDialog($AppController, $Page);


printf(
    '<form method="post" id="documentform" name="documentform" action="%s">'
	,SLink::link(array('edit' => (isset($Page) && $Page instanceof CPage)? $Page->Alias :''))
);
if(isset($Page))
{
	try{
		$panel = new WSidePanel();
		$panel->setMode(
		    WSidePanel::MEDIA_LOOKUP|
		    WSidePanel::CONTENT_LOOKUP|
		    WSidePanel::PROPERTY_EDIT|
		    WSidePanel::HELPER|
		    WSidePanel::PERMISSIONS);
		$panel->setTargetContent($Page);
		echo $panel;
		if($Page instanceof CPage && $Page->isModified())
		{
			$Page->Save();
		}
	}
	catch(Exception $e){
		echo $e->getTraceAsString();
	}	
}
?>