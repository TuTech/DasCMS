<?php
/************************************************
* Bambus CMS 
* Created:     24. Okt 08
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: feed controller
************************************************/
$AppController = BAppController::getControllerForID('org.bambuscms.applications.feeds');

$allowEdit = true;
$FileOpened = false;

$editExist = (RURL::has('edit')) && CFeed::Exists(RURL::get('edit'));
$Feed = null;
//delete
if($editExist && RSent::get('delete') != '' && PAuthorisation::has('org.bambuscms.content.cfeed.delete'))
{
	CFeed::Delete(RURL::get('edit'));
	$editExist = false;
}
if(RSent::get('action') == 'delete' && PAuthorisation::has('org.bambuscms.content.cfeed.delete'))
{
	foreach (RSent::data() as $k => $v) 
	{
		if(substr($k,0,7) == 'select_' && !empty($v))
		{
			//delete
			CFeed::Delete(substr($k,7));
		}
	}
}

//create
elseif(RSent::hasValue('create') && PAuthorisation::has('org.bambuscms.content.cfeed.create'))
{
	$Title = RSent::get('create');
	$Feed = CFeed::Create($Title);
}

//open for editing
elseif($editExist && PAuthorisation::has('org.bambuscms.content.cfeed.change'))
{
	$Feed = CFeed::Open(RURL::get('edit'));
}

//save data
if(isset($Feed) && $Feed instanceof CFeed && PAuthorisation::has('org.bambuscms.content.cfeed.change'))
{
	if(RSent::has('filename'))
	{
		$Feed->Title = RSent::get('filename');
	}
}
echo new WOpenDialog($AppController, $Feed);


printf(
    '<form method="post" id="documentform" name="documentform" action="%s">'
	,SLink::link(array('edit' => (isset($Feed) && $Feed instanceof CFeed)? $Feed->Alias :''))
);
if(isset($Feed))
{
	try{
		echo new WSidebar($Feed);
		if($Feed instanceof CFeed && $Feed->isModified())
		{
			$Feed->Save();
		}
	}
	catch(Exception $e){
		//echo $e->getTraceAsString();
	}	
}
?>