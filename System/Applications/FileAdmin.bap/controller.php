<?php
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
$succesfullUpload = false;
$notAllowed = array('php', 'cgi', 'php3', 'php4', 'php5', 'php6', 'asp', 'aspx', 'pl', 'py', 'phtml');
if(isset($_FILES['bambus_file']['name']) && BAMBUS_GRP_CREATE){ 
    // we have got an upload
    if(file_exists($Bambus->pathTo('download').basename($_FILES['bambus_file']['name'])) && empty($post['bambus_overwrite_file'])){
        SNotificationCenter::alloc()->init()->report('warning', 'upload_failed_because_file_already_exists');
    }else{
        //file does not exist or we are allowed to overwrite it
        $tmp = explode('.', $_FILES['bambus_file']['name']);
        $suffix = strtolower(array_pop($tmp));
        $tmp = null;
        if(!in_array($suffix, $notAllowed)){
            //we like him
            if(@move_uploaded_file($_FILES['bambus_file']['tmp_name'], $Bambus->pathTo('download').basename(utf8_decode($_FILES['bambus_file']['name'])))){
                //i like to move it move it
                SNotificationCenter::alloc()->init()->report('message', 'file_uploaded');
                chmod($Bambus->pathTo('download').basename(utf8_decode($_FILES['bambus_file']['name'])), 0666);
                $succesfullUpload = basename(utf8_decode($_FILES['bambus_file']['name']));
                $FS = FileSystem::alloc();
                $FS->init();
                $FS->writeLine($Bambus->pathTo('log').'files.log', sprintf("%s\t%s\t%s\t%s", date('r'), BAMBUS_USER, 'upload', $_FILES['bambus_file']['name']));
            }else{
                SNotificationCenter::alloc()->init()->report('warning', 'uploded_failed');
            }
        }else{
            //run away and scream
            SNotificationCenter::alloc()->init()->report('warning', 'uploded_failed');
        }
        
    }
}
if(!empty($post['action']) && $post['action'] == 'delete' && BAMBUS_GRP_DELETE)
{
	$files = $Bambus->FileSystem->getFiles('download', array('php', 'cgi', 'php3', 'php4', 'php5', 'php6', 'asp', 'aspx', 'pl'), false);
	foreach($files as $file)
	{
		if(!empty($post['select_'.md5($file)]))
		{
	        if(unlink($Bambus->pathTo('download').$file)){
	            SNotificationCenter::alloc()->init()->report('message', 'file_deleted');
                $FS = FileSystem::alloc();
                $FS->init();
                $FS->writeLine($Bambus->pathTo('log').'files.log', sprintf("%s\t%s\t%s\t%s", date('r'), BAMBUS_USER, 'delete',$file));
	        }else{
	            SNotificationCenter::alloc()->init()->report('warning', 'could_not_delete_file');
	        }
			
		}
	}
}
?>