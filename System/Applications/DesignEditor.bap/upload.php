<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: image upload form
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
$maxPost = DFileSystem::returnBytes(ini_get('upload_max_filesize'));
$freeSpace = disk_free_space(SPath::IMAGES);
$requestmessage = SLocalization::get('supported_file_types').': '.implode(', ', $allowed)
		.'<br />'.SLocalization::get('maximum_upload_size').': '.DFileSystem::formatSize($maxPost)
		.'<br />'.SLocalization::get('free_disk_space').': '.DFileSystem::formatSize($freeSpace);
        $image = $this->icon('css', '', 'mimetype','medium', $system);
echo '<div class=""><span style="float:left;padding:4px;padding-top:0px;">'.$image.'</span>'.$requestmessage.'<br class="clear" /></div>';
	
	
	
echo LGui::beginMultipartForm(array(), 'documentform');
printf('<input type="hidden" name="MAX_FILE_SIZE" value="%s">', ($maxPost > 0) ? $maxPost : 1000000000);
echo LGui::beginTable("uploadform");
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
			? html_entity_decode(SLink::link(array('render' => $succesfullUpload, 'path' => 'design'),'thumbnail.php'))
			//file icon
			: (file_exists(WIcon::pathFor($suffix, 'mimetype',WIcon::LARGE)))
				? WIcon::pathFor($suffix, 'mimetype',WIcon::LARGE)
				: WIcon::pathFor('file', 'mimetype',WIcon::LARGE)
		)
		,htmlentities($succesfullUpload)
		,htmlentities($succesfullUpload));
}
  

echo LGui::endTable();
echo LGui::beginTable("uploadstatus", 'hide');
?>
    <tr valign="middle">
        <td>
            <img src="<?php echo WIcon::pathFor('loading', 'animation',WIcon::EXTRA_SMALL);?>" alt="" />
        </td>
        <td>
            <?php SLocalization::out('uploading');?>
        </td>
    </tr>
<?php
echo LGui::endTable();
echo LGui::endMultipartForm();

?>
