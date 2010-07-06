<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.filemanager
 * @since 2006-10-16
 * @version 1.0
 */
$File = SApplication::getControllerContent();
if($File != null && $File instanceof Interface_Content)
{
    echo new WScript("var is_in_content_mode = true;");
    echo new WContentTitle($File);
	if(WImage::supportedMimeType($File->getMimeType()))
	{
	    $img = $File->PreviewImage;
	    printf('<div class="previewImage" ondblclick="org.bambuscms.wopenfiledialog.openAlias(\'\');">%s</div>',$img->scaled(640,480,WImage::MODE_SCALE_TO_MAX));
	}
	else
	{
	    echo '<span ondblclick="org.bambuscms.wopenfiledialog.openAlias(\'\');">'.$File->Icon.'</span>';
	}
	printf(
	    '<input type="checkbox" style="display:none;" name="select_%s" id="select_%s" checked="checked" />'
		,$File->Alias
	    ,$File->Alias
	);
	$RelCrtl = Controller_ContentRelationManager::getInstance();
	$retainCount = $RelCrtl->getRetainCount($File->getAlias());
	$tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'attributes');
	$tbl->addRow(array('attribute','value'));
	$tbl->addRow(array('original_file_name',$File->FileName));
	$tbl->addRow(array('mime_type',$File->MimeType));
	$tbl->addRow(array('md5_sum',$File->MD5Sum));
	$tbl->addRow(array('retain_count',$retainCount));
	if(WImage::supportedMimeType($File->MimeType))
	{
	    SErrorAndExceptionHandler::muteErrors();
	    $info = getimagesize($File->getRawDataPath());
	    SErrorAndExceptionHandler::reportErrors();
	    if($info)
	    {
        	$tbl->addRow(array('width',$info[0].'px'));
        	$tbl->addRow(array('height',$info[1].'px'));
	    }
	}
	$tbl->render();
	if(RSent::hasValue('reshow_upload_dialogue')){
		echo new WScript(sprintf('org.bambuscms.autorun.register(function(){Upload(true, %s);});', RSent::hasValue('autopublish_upload')?'true':'false'));
	}

}
else
{
    echo new WScript("
    	$('App-Hotkey-CTRL-s').parentNode.removeChild($('App-Hotkey-CTRL-s'));
    	$('App-Hotkey-CTRL-D').parentNode.removeChild($('App-Hotkey-CTRL-D'));
    	$('App-Hotkey-CTRL-X').parentNode.removeChild($('App-Hotkey-CTRL-X'));
    	org.bambuscms.app.hotkeys.unregister('CTRL-s');
    	org.bambuscms.app.hotkeys.unregister('CTRL-D');
    	org.bambuscms.app.hotkeys.unregister('CTRL-X');
    	var is_in_content_mode = false;
    	");
}

?>
