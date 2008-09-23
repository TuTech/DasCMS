<?php
/************************************************
* Bambus CMS 
* Created:     11. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: css controller
************************************************/
$allowed = array('css', 'gpl', 'jpeg','jpg','png','gif','svg','mng','eps','ps','tif','tiff','psd','ai','pcx','wmf');
if(isset($_FILES['bambus_image_file']['name']) && PAuthorisation::has('org.bambus-cms.layout.image.create'))
{ 
    // we have got an upload
    if(file_exists(SPath::DESIGN.basename($_FILES['bambus_image_file']['name'])) 
      && RSent::hasValue('bambus_overwrite_image_file'))
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
            if(move_uploaded_file($_FILES['bambus_image_file']['tmp_name']
                ,SPath::DESIGN.basename(utf8_decode($_FILES['bambus_image_file']['name']))))
            {
                //i like to move it move it
                SNotificationCenter::report('message', 'file_uploaded');
                chmod(SPath::DESIGN.basename(utf8_decode($_FILES['bambus_image_file']['name'])), 0666);
                $succesfullUpload = basename(utf8_decode($_FILES['bambus_image_file']['name']));
                //create thumbnail image
                $image = SPath::DESIGN.basename(utf8_decode($_FILES['bambus_image_file']['name']));
                $uploadIsImage = ($suffix != 'css' && $suffix != 'gpl');
            }
            else
            {
                SNotificationCenter::report('warning', 'uploded_failed');
            }
        }
        else
        {
            //run away and scream
            SNotificationCenter::report('warning', 'upload_failed_because_of_unsupported_file_type');
        }
        
    }
}

$doNotDelete = array('page.tpl');
$Files = DFileSystem::FilesOf(SPath::TEMPLATES, '/\.tpl/i');
$File = null;

//has edit?
if(RURL::hasValue('edit') && file_exists(SPath::TEMPLATES.basename(RURL::get('edit'))))
{
    $File = basename(RURL::get('edit'));
}
//create new file
if(RURL::get('_action') == 'create' && PAuthorisation::has('org.bambus-cms.layout.template.create'))
{
	$i = 0;
	$sep = '';
	while(file_exists(SPath::TEMPLATES.'new_template'.$sep.'.tpl'))
	{
	    $sep = '-'.++$i;
	}
	$File = 'new_template'.$sep.'.tpl';
	DFileSystem::Save(SPath::TEMPLATES.$File, '<!-- '.SLocalization::get('new_template').' -->');
}
//delete file
elseif($File != null && RURL::get('_action') == 'delete' && PAuthorisation::has('org.bambus-cms.layout.template.delete'))
{
    if(in_array($File, $doNotDelete))
    {
        SNotificationCenter::report('warning', 'file_cant_be_deleted');
    }
    else
    {
        unlink(SPath::TEMPLATES.$File);
	    SNotificationCenter::report('message', 'file_deleted');
	    $File = null;
    }
}
//rename file
elseif($File != null && RSent::hasValue('filename') && substr($File,0,-4) != RSent::get('filename'))
{
    if(file_exists(SPath::TEMPLATES.basename(RSent::get('filename')).'.tpl'))
    {
        SNotificationCenter::report('warning', 'cannot_rename_file_target_already_exists');
    }
    elseif(in_array($File, $doNotDelete))
    {
         SNotificationCenter::report('warning', 'file_cant_be_renamed');
    }
    else
    {
        rename(SPath::TEMPLATES.$File, SPath::TEMPLATES.basename(RSent::get('filename')).'.tpl');
	    SNotificationCenter::report('message', 'file_renamed');
	    $File = basename(RSent::get('filename')).'.tpl';
    }
}
//save file
elseif($File != null && RSent::has('content') && PAuthorisation::has('org.bambus-cms.layout.template.change'))
{
    //do the save operation
    if(DFileSystem::Save(SPath::TEMPLATES.$File, RSent::get('content','utf-8')))
    {
    	SNotificationCenter::report('message', 'saved');
    }
    else
    {
    	SNotificationCenter::report('warning', 'saving_failed');
    }
}
?>