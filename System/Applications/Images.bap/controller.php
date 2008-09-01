<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
$allowEdit = true;
$allowed = array('jpeg','jpg','png','gif','svg','mng','eps','ps','tif','tiff','psd','ai','pcx','wmf');
$Files = DFileSystem::FilesOf($Bambus->pathTo('image'), '/\.('.implode('|', $allowed).')/i');

//////////
//upload//
//////////
$succesfullUpload = false;
$uploadIsImage = false;
if(isset($_FILES['bambus_image_file']['name']) && BAMBUS_GRP_CREATE)
{ 
    // we have got an upload
    if(file_exists($Bambus->pathTo('image').basename($_FILES['bambus_image_file']['name'])) 
      && empty($post['bambus_overwrite_image_file']))
    {
        SNotificationCenter::alloc()->init()->report('warning', 'upload_failed_because_file_already_exists');
    }
    else
    {
        //file does not exist or we are allowed to overwrite it
        //the suffixes of nice image types are:
        
        //everything else we treat as ordinary file
        $tmp = explode('.', utf8_decode($_FILES['bambus_image_file']['name']));
        $suffix = strtolower(array_pop($tmp));
        $tmp = null;
        if(in_array($suffix, $allowed))
        {
            //we like him
            if(move_uploaded_file($_FILES['bambus_image_file']['tmp_name'], $Bambus->pathTo('image').basename(utf8_decode($_FILES['bambus_image_file']['name']))))
            {
                //i like to move it move it
                SNotificationCenter::alloc()->init()->report('message', 'file_uploaded');
                chmod($Bambus->pathTo('image').basename(utf8_decode($_FILES['bambus_image_file']['name'])), 0666);
                $succesfullUpload = basename(utf8_decode($_FILES['bambus_image_file']['name']));
                //create thumbnail image
                $image = $Bambus->pathTo('image').basename(utf8_decode($_FILES['bambus_image_file']['name']));
                $uploadIsImage = ($suffix != 'css' && $suffix != 'gpl');
            }
            else
            {
                SNotificationCenter::alloc()->init()->report('message', 'uploded_failed');
            }
        }
        else
        {
            //run away and scream
            SNotificationCenter::alloc()->init()->report('warning', 'upload_failed_because_of_unsupported_file_type');
        }
        
    }
}

if(count($Files) > 0)
{
	//////////////////
	//manager delete//
	//////////////////
	if(!empty($post['action']) && $post['action'] == 'delete' && BAMBUS_GRP_DELETE)
	{
		foreach($Files as $file)
		{
			if(!empty($post['select_'.md5($file)]))
			{
		        if(@unlink($Bambus->pathTo('image').$file)){
		        	SNotificationCenter::alloc()->init()->report('message', 'file_deleted');
		        }else{
		            SNotificationCenter::alloc()->init()->report('warning', 'could_not_delete_file');
		        }
				
			}
		}
	}
}	
?>
