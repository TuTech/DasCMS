<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: image upload form
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
$maxPost = $Bambus->returnBytes(ini_get('upload_max_filesize'));
$freeSpace = disk_free_space($Bambus->pathTo('image'));
$requestmessage = SLocalization::get('supported_file_types').': '.implode(', ', $allowed)
		.'<br />'.SLocalization::get('maximum_upload_size').': '.$Bambus->formatSize($maxPost)
		.'<br />'.SLocalization::get('free_disk_space').': '.$Bambus->formatSize($freeSpace);
        $image = $this->icon('css', '', 'mimetype','medium', $system);
echo '<div class=""><span style="float:left;padding:4px;padding-top:0px;">'.$image.'</span>'.$requestmessage.'<br class="clear" /></div>';
	
	
	
echo $Bambus->Gui->beginMultipartForm(array(), 'documentform');
printf('<input type="hidden" name="MAX_FILE_SIZE" value="%s">', ($maxPost > 0) ? $maxPost : 1000000000);
echo $Bambus->Gui->beginTable("uploadform");
?>
    <tr>
    	<th colspan="2"><?php SLocalization::out('settings');?></th>
    </tr>
    <tr valign="middle">
    	<th class="left_th">
    		<?php SLocalization::out('file');?>
    	</th>
        <td>
            <input name="bambus_image_file" type="file" />
        </td>
    </tr>    
    <tr valign="middle">
    	<th class="left_th">
    		<?php SLocalization::out('allow_overwrite');?>
    	</th>
        <td>
            <input name="bambus_overwrite_image_file" type="checkbox" />
        </td>
    </tr>
<?php
if($succesfullUpload != false)
{
	printf(
		'<tr><th class="left_th">%s</th><td><div class="thumbnail"><img src="%s" alt="" title="%s" />%s</div></td></tr>'
		,SLocalization::get('recently_uploaded_file')
		,(($uploadIsImage) 
			//preview image
			//? 'thumbnail.php'.$Bambus->Linker->createQueryString(array('render' => $succesfullUpload, 'path' => 'design'))
			? html_entity_decode($Bambus->Linker->createQueryString(array('render' => $succesfullUpload, 'path' => 'design'),false,'thumbnail.php'))
			//file icon
			: (file_exists($Bambus->Gui->iconPath($suffix, $suffix, 'mimetype','large')))
				? $Bambus->Gui->iconPath($suffix, $suffix, 'mimetype','large')
				: $Bambus->Gui->iconPath('file', 'file', 'mimetype','large')
		)
		,htmlentities($succesfullUpload)
		,htmlentities($succesfullUpload));
}
  

echo $Bambus->Gui->endTable();
echo $Bambus->Gui->beginTable("uploadstatus", 'hide');
?>
    <tr valign="middle">
        <td>
            <img src="<?php echo $Bambus->Gui->iconPath('loading', 'uploading', 'animation','extra-small');?>" alt="" />
        </td>
        <td>
            <?php SLocalization::out('uploading');?>
        </td>
    </tr>
<?php
echo $Bambus->Gui->endTable();
echo $Bambus->Gui->endMultipartForm();

?>
