<?php
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
if(RSent::get('action') == 'delete' && PAuthorisation::has('org.bambuscms.content.cfile.delete'))
{
	$files = array_keys(CFile::Index());
	foreach($files as $file)
	{
		if(RSent::hasValue('select_'.$file))
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
?>