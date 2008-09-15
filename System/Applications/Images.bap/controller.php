<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
$allowEdit = true;
$allowed = array('jpeg','jpg','png','gif','svg','mng','eps','ps','tif','tiff','psd','ai','pcx','wmf');
$Files = DFileSystem::FilesOf(SPath::IMAGES, '/\.('.implode('|', $allowed).')/i');

//////////
//upload//
//////////
$succesfullUpload = false;
$uploadIsImage = false;
if(isset($_FILES['bambus_image_file']['name']) && PAuthorisation::has('org.bambus-cms.image.file.create'))
{ 
    // we have got an upload
    if(file_exists(SPath::IMAGES.basename($_FILES['bambus_image_file']['name'])) 
      && !RSent::hasValue('bambus_overwrite_image_file'))
    {
        SNotificationCenter::report('warning', 'upload_failed_because_file_already_exists');
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
            if(move_uploaded_file($_FILES['bambus_image_file']['tmp_name'], SPath::IMAGES.basename(utf8_decode($_FILES['bambus_image_file']['name']))))
            {
                //i like to move it move it
                SNotificationCenter::report('message', 'file_uploaded');
                chmod(SPath::IMAGES.basename(utf8_decode($_FILES['bambus_image_file']['name'])), 0666);
                $succesfullUpload = basename(utf8_decode($_FILES['bambus_image_file']['name']));
                //create thumbnail image
                $image = SPath::IMAGES.basename(utf8_decode($_FILES['bambus_image_file']['name']));
                $uploadIsImage = ($suffix != 'css' && $suffix != 'gpl');
            }
            else
            {
                SNotificationCenter::report('message', 'uploded_failed');
            }
        }
        else
        {
            //run away and scream
            SNotificationCenter::report('warning', 'upload_failed_because_of_unsupported_file_type');
        }
        
    }
}

if(count($Files) > 0)
{
	//////////////////
	//manager delete//
	//////////////////
	if(RSent::hasValue('action') && RSent::get('action') == 'delete' && PAuthorisation::has('org.bambus-cms.image.file.delete'))
	{
		foreach($Files as $file)
		{
			if(!RSent::hasValue('select_'.md5($file)))
			{
		        if(@unlink(SPath::IMAGES.$file)){
		        	SNotificationCenter::report('message', 'file_deleted');
		        }else{
		            SNotificationCenter::report('warning', 'could_not_delete_file');
		        }
				
			}
		}
	}
}	
?>
