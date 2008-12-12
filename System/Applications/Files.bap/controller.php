<?php
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
	    SNotificationCenter::report('warning', 'upload_failed'.$e->getMessage());
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
                DFileSystem::Append(SPath::LOGS.'files.log', sprintf("%s\t%s\t%s\t%s\n", date('r'), PAuthentication::getUserID(), 'delete',$file));
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
if($File != null && $File instanceof BContent)
{
	try{
    	if(RSent::has('filename'))
    	{
    		$File->Title = RSent::get('filename', 'utf-8');
    	}
		$panel = new WSidePanel();
		$panel->setMode(
		    WSidePanel::PROPERTY_EDIT|
		    WSidePanel::HELPER|
		    WSidePanel::PERMISSIONS);
		$panel->setTargetContent($File);
		echo $panel;
		if($File->isModified())
		{
			$File->Save();
		}
	}
	catch(Exception $e){
		echo $e->getTraceAsString();
	}	
}

$ofd = new WOpenDialog($AppController, $File);
$ofd->autoload(false);
echo $ofd;
?>