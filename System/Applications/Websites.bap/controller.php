<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
$allowEdit = true;

$FileOpened = false;
$mp = MPageManager::alloc()->init();

$editExist = (RURL::has('edit')) && $mp->Exists(RURL::get('edit'));
$Page = null;
//delete
if($editExist && RSent::get('delete') != '' && PAuthorisation::has('org.bambus-cms.content.cpage.delete'))
{
	$mp->Delete(RURL::get('edit'));
	$editExist = false;
}
if(RSent::get('action') == 'delete' && PAuthorisation::has('org.bambus-cms.content.cpage.delete'))
{
	foreach (RSent::data() as $k => $v) 
	{
		if(substr($k,0,7) == 'select_' && !empty($v))
		{
			//delete
			$mp->Delete(substr($k,7));
		}
	}
}

//create
elseif(RSent::hasValue('create') && PAuthorisation::has('org.bambus-cms.content.cpage.create'))
{
	$Title = RSent::get('create');
	$Page = $mp->Create($Title);
	$Page->Content = $Title;
}

//open for editing
elseif($editExist && PAuthorisation::has('org.bambus-cms.content.cpage.change'))
{
	$Page = $mp->Open(RURL::get('edit'));
}

//save data
if(isset($Page) && $Page instanceof CPage && PAuthorisation::has('org.bambus-cms.content.cpage.change'))
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

echo new WScript('org.bambuscms.wopenfiledialog.setSource({\'controller\':\'org.bambuscms.applications.websiteeditor\',\'call\':\'provideOpenDialogData\'});'.
                 'org.bambuscms.wopenfiledialog.prepareLinks("'.
                 SLink::link(array('edit' => ''))
                 .'","");');
if($Page == null)
{
    echo new WScript('org.bambuscms.wopenfiledialog.closable = false;'.
                     'org.bambuscms.wopenfiledialog.show();');
}

$OFD = new WOpenFileDialog();
$OFD->registerCategory('page');
foreach($mp->Index as $item => $name)
{
    $OFD->addItem('page',$name,SLink::link(array('edit' => $item)),'website', ' ');
}
//$OFD->render();

printf(
    '<form method="post" id="documentform" name="documentform" action="%s">'
	,SLink::link(array('edit' => (isset($Page) && $Page instanceof CPage)? $Page->Id :''))
);
if(BAMBUS_APPLICATION_TAB != 'manage' && isset($Page))
{
	try{
		echo new WSidebar($Page);
		if($Page instanceof CPage)
		{
			//FIXME - do not do allways
			$Page->Save();
		}
	}
	catch(Exception $e){
		echo $e->getTraceAsString();
	}	
	
}
?>