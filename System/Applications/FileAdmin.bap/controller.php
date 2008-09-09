<?php
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
$succesfullUpload = false;
$notAllowed = array('php', 'cgi', 'php3', 'php4', 'php5', 'php6', 'asp', 'aspx', 'pl', 'py', 'phtml');
if(isset($_FILES['bambus_file']['name']) && BAMBUS_GRP_CREATE){ 
    // we have got an upload
    if(file_exists(SPath::DOWNLOADS.basename($_FILES['bambus_file']['name'])) && !RSent::hasValue('bambus_overwrite_file')){
        SNotificationCenter::report('warning', 'upload_failed_because_file_already_exists');
    }else{
        //file does not exist or we are allowed to overwrite it
        $tmp = explode('.', $_FILES['bambus_file']['name']);
        $suffix = strtolower(array_pop($tmp));
        $tmp = null;
        if(!in_array($suffix, $notAllowed)){
            //we like him
            if(@move_uploaded_file($_FILES['bambus_file']['tmp_name'], SPath::DOWNLOADS.basename(utf8_decode($_FILES['bambus_file']['name'])))){
                //i like to move it move it
                SNotificationCenter::report('message', 'file_uploaded');
                chmod(SPath::DOWNLOADS.basename(utf8_decode($_FILES['bambus_file']['name'])), 0666);
                $succesfullUpload = basename(utf8_decode($_FILES['bambus_file']['name']));
                DFileSystem::Append(SPath::LOGS.'files.log', sprintf("%s\t%s\t%s\t%s\n", date('r'), BAMBUS_USER, 'upload', $_FILES['bambus_file']['name']));
            }else{
                SNotificationCenter::report('warning', 'uploded_failed');
            }
        }else{
            //run away and scream
            SNotificationCenter::report('warning', 'uploded_failed');
        }
        
    }
}
if(RSent::get('action') == 'delete' && BAMBUS_GRP_DELETE)
{
	$files = DFileSystem::FilesOf(SPath::DOWNLOADS, '/\.(?!(php[0-9]?|aspx?|pl|phtml|cgi))$/i');
	foreach($files as $file)
	{
		if(RSent::hasValue('select_'.md5($file)))
		{
	        if(unlink(SPath::DOWNLOADS.$file)){
	            SNotificationCenter::report('message', 'file_deleted');
                DFileSystem::Append(SPath::LOGS.'files.log', sprintf("%s\t%s\t%s\t%s\n", date('r'), BAMBUS_USER, 'delete',$file));
	        }else{
	            SNotificationCenter::report('warning', 'could_not_delete_file');
	        }
			
		}
	}
}
?>