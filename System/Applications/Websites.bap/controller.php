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
if($editExist && RSent::get('delete') != '' && PAuthorisation::has('org.bambuscms.content.cpage.delete'))
{
	CPage::Delete(RURL::get('edit'));
	$editExist = false;
}
if(RSent::get('action') == 'delete' && PAuthorisation::has('org.bambuscms.content.cpage.delete'))
{
	foreach (RSent::data() as $k => $v) 
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
	$Title = RSent::get('create');
	$Page = CPage::Create($Title);
	$Page->Content = $Title;
}

//open for editing
elseif($editExist && PAuthorisation::has('org.bambuscms.content.cpage.change'))
{
	$Page = CPage::Open(RURL::get('edit'));
}

//save data
if(isset($Page) && $Page instanceof CPage && PAuthorisation::has('org.bambuscms.content.cpage.change'))
{
	if(RSent::has('content'))
	{
		$Page->Content = RSent::get('content');
	}
	if(RSent::has('filename'))
	{
		$Page->Title = RSent::get('filename');
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
		echo new WSidebar($Page);
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