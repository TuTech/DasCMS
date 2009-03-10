<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.filemanager
 * @since 2006-10-16
 * @version 1.0
 */
$AppController = BAppController::getControllerForID('org.bambuscms.applications.files');

if(RSent::get('delete', 'utf-8') != '' && PAuthorisation::has('org.bambuscms.content.cfile.delete'))
{
	CFile::Delete(RURL::get('edit', 'utf-8'));
}

$succesfullUpload = false;
if(RFiles::has('CFile') && PAuthorisation::has('org.bambuscms.content.cfile.create')){ 
//create here
	try
	{
	    $f = CFile::Create('');
	    SNotificationCenter::report('message', 'file_uploaded');
	}
	catch(Exception $e)
	{
	    SNotificationCenter::report('warning', 'upload_failed '.$e->getMessage());
	    echo $e->getTraceAsString();
	}
}
if(RSent::get('action', 'utf-8') == 'delete' && PAuthorisation::has('org.bambuscms.content.cfile.delete'))
{
	$files = array_keys(CFile::Index());
	foreach($files as $file)
	{
		if(RSent::hasValue('select_'.md5($file)))
		{
		    //delete here
	        if(CFile::Delete($file)){
	            SNotificationCenter::report('message', 'file_deleted');
	        }else{
	            SNotificationCenter::report('warning', 'could_not_delete_file');
	        }
			
		}
	}
}
$File = null;
if(RURL::hasValue('edit'))
{
    try{
        $File = CFile::Open(RURL::get('edit'));
    }catch (Exception $e){/*$File stays null*/}
}
printf(
    '<form method="post" id="documentform" name="documentform" action="%s">'
	,SLink::link(array('edit' => (isset($File) && $File instanceof CFile)? $File->Alias :''))
);

if($File != null && $File instanceof BContent && RSent::has('filename'))
{    
	$File->Title = RSent::get('filename', 'utf-8');
}
$panel = WSidePanel::alloc()->init();
$panel->setMode(
    WSidePanel::PROPERTY_EDIT|
    WSidePanel::HELPER|
    WSidePanel::PERMISSIONS|
    WSidePanel::RETAIN);
if($File != null && $File instanceof BContent)
{
    $panel->setTargetContent($File);
}
$panel->processInputs();
if($File != null && $File instanceof BContent && $File->isModified())
{
	$File->Save();
}

$ofd = new WOpenDialog($AppController, $File);
$ofd->autoload(false);
echo $ofd;
?>