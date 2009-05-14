<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.filemanager
 * @since 2006-10-16
 * @version 1.0
 */
$File = SApplication::getControllerContent();
if($File != null && $File instanceof BContent)
{

    echo new WScript("
    	$('CommandBarPanel_selection').parentNode.removeChild($('CommandBarPanel_selection'));
    	org.bambuscms.app.hotkeys.unregister('CTRL-a');
    	org.bambuscms.app.hotkeys.unregister('CTRL-e');
    	var is_in_content_mode = true;
    	");
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
	$retains = WImage::getRetainCounts();
	$tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'attributes');
	$tbl->addRow(array('attribute','value'));
	$tbl->addRow(array('original_file_name',$File->FileName));
	$tbl->addRow(array('mime_type',$File->MimeType));
	$tbl->addRow(array('md5_sum',$File->MD5Sum));
	$tbl->addRow(array('retain_count',isset($retains[$File->Id]) ? $retains[$File->Id] : '0'));
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

}
else
{
    echo new WScript("
    	$('App-Hotkey-CTRL-s').parentNode.removeChild($('App-Hotkey-CTRL-s'));
    	$('App-Hotkey-CTRL-u').parentNode.removeChild($('App-Hotkey-CTRL-u'));
    	org.bambuscms.app.hotkeys.unregister('CTRL-s');
    	org.bambuscms.app.hotkeys.unregister('CTRL-u');
    	var is_in_content_mode = false;
    	");
        echo '<input type="hidden" name="action" value="delete" />';
    $files = CFile::Index();
    
    $itemTemplate = "<a name=\"{id}\" title=\"{title}\" id=\"{id}\" ondblclick=\"org.bambuscms.wopenfiledialog.openAlias('{alias}');\" href=\"javascript:selectImage('{id}');\">
        <div class=\"helper\"><span class=\"editHelper\" onclick=\"org.bambuscms.wopenfiledialog.openAlias('{alias}');\"></span></div>
    	{retainCount}
    	{preview}
        <input type=\"checkbox\" name=\"select_{id}\" id=\"select_{id}\" />
        {name}
    </a>";
    printf('<h2>%s</h2>', SLocalization::get('files'));
    $flowLayout = new WFlowLayout();
    $flowLayout->setAdditionalCSSClasses('WFlowLayoutImage');
    $retains = WImage::getRetainCounts();
    foreach($files as $alias => $data){
        list($Dtitle, $Dpubdate, $Dtype, $Did) = $data;
        $suffix = 'CFile';
        $bigIcon = (file_exists(WIcon::pathFor($suffix, 'mimetype', WIcon::LARGE))) ? $suffix : 'file';
        $prev = (WImage::supportedMimeType($Dtype))
            ? (WImage::forCFileData($Did,$Dtype,$alias,$Dtitle)->scaled(128, 128,WImage::MODE_FORCE,WImage::FORCE_BY_FILL))
            : sprintf('<img src="%s" class="mime-icon" alt="%s" />', WIcon::pathForMimeIcon($Dtype, WIcon::LARGE), $Dtype);
        
        $output = array('realname'  => htmlentities($Dtitle, ENT_QUOTES, 'UTF-8'),
            'preview' => $prev,
            'editIcon' => WIcon::pathFor('edit','action',WIcon::EXTRA_SMALL),
            'linktarget' => '_blank',
            'retainCount' => isset($retains[$Did]) ? sprintf('<span class="retainBatch retainBatch-%d">%s</span>', strlen($retains[$Did]), $retains[$Did]) : '',
            'id' => $alias,
            'alias' => $alias,
            'title' => htmlentities($data[0], ENT_QUOTES, 'UTF-8'),
            'name' => htmlentities($data[0], ENT_QUOTES, 'UTF-8')
        );
        $tpl = new WTemplate($itemTemplate, WTemplate::STRING);
        $tpl->setEnvironment($output);
        $flowLayout->addItem($tpl);
    }
    $flowLayout->render();
    echo new WScript('hideInputs();');
}

?>
