<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.filemanager
 * @since 2006-10-16
 * @version 1.0
 */
$File = SApplication::getControllerContent();
if($File != null && $File instanceof CFile)
{
    echo "<script type=\"text/javascript\">var is_in_content_mode = true;</script>";
    echo new View_UIElement_ContentTitle($File);
	if(View_UIElement_Image::supportedMimeType($File->getMimeType()))
	{
	    $img = $File->getPreviewImage();
	    printf('<div class="previewImage">%s</div>',$img->scaled(640,480,View_UIElement_Image::MODE_SCALE_TO_MAX));
	}
	else
	{
	    echo '<span>'.$File->getIcon().'</span>';
	}
	printf(
	    '<input type="checkbox" style="display:none;" name="select_%s" id="select_%s" checked="checked" />'
		,$File->getAlias()
	    ,$File->getAlias()
	);
	$RelCrtl = Controller_ContentRelationManager::getInstance();
	$retainCount = $RelCrtl->getRetainCount($File->getAlias());
	$tbl = new View_UIElement_Table(View_UIElement_Table::HEADING_TOP|View_UIElement_Table::HEADING_LEFT, 'attributes');
	$tbl->addRow(array('attribute','value'));
	$tbl->addRow(array('original_file_name',$File->getFileName()));
	$tbl->addRow(array('mime_type',$File->getMimeType()));
	$tbl->addRow(array('md5_sum',$File->getMD5Sum()));
	$tbl->addRow(array('retain_count',$retainCount));
	if(View_UIElement_Image::supportedMimeType($File->getMimeType()))
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
		echo sprintf('<script type=\"text/javascript\">org.bambuscms.autorun.register(function(){Upload(true, %s);});</script>', RSent::hasValue('autopublish_upload')?'true':'false');
	}

}
else
{
    echo "<script type=\"text/javascript\">
    	$('App-Hotkey-CTRL-s').parentNode.removeChild($('App-Hotkey-CTRL-s'));
    	$('App-Hotkey-CTRL-D').parentNode.removeChild($('App-Hotkey-CTRL-D'));
    	$('App-Hotkey-CTRL-X').parentNode.removeChild($('App-Hotkey-CTRL-X'));
    	org.bambuscms.app.hotkeys.unregister('CTRL-s');
    	org.bambuscms.app.hotkeys.unregister('CTRL-D');
    	org.bambuscms.app.hotkeys.unregister('CTRL-X');
    	var is_in_content_mode = false;
    	</script>";
}

?>
