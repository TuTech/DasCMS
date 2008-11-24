<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/

if($File != null && $File instanceof BContent)
{
    if(isset($panel) && $panel->hasWidgets())
    {
        echo '<div id="objectInspectorActiveFullBox">';
    }
    echo new WScript("org.bambuscms.gui.hideCommandPanels(['multi_delete', 'multi_select', 'multi_view']);");
	printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2><img src="%s" alt="" />'
    	, htmlentities($File->Title, ENT_QUOTES, 'UTF-8')
    	, htmlentities($File->Title, ENT_QUOTES, 'UTF-8')
    	, WIcon::pathForMimeIcon($File->MimeType, WIcon::LARGE)
	);
	printf(
	    '<input type="checkbox" style="display:none;" name="select_%s" id="select_%s" checked="checked" />'
		,$File->Alias
	    ,$File->Alias
	);
	$tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'attributes');
	$tbl->addRow(array('attribute','value'));
	$tbl->addRow(array('original_file_name',$File->FileName));
	$tbl->addRow(array('mime_type',$File->MimeType));
	$tbl->addRow(array('md5_sum',$File->MD5Sum));
	$tbl->render();
    if(isset($panel) && $panel->hasWidgets())
    {
        echo '</div>';
    }
}
else
{
    echo new WScript("org.bambuscms.gui.hideCommandPanels(['singe_object_edit']);");
    echo LGui::hiddenInput('action', 'delete');
    $files = CFile::Index();
    
    $itemTemplate = "<a name=\"{id}\" title=\"{title}\" id=\"{id}\" href=\"javascript:selectImage('{id}');\">
        <img src=\"{icon}\" title=\"{title}\" id=\"img_{id}\" />
        <input type=\"checkbox\" name=\"select_{id}\" id=\"select_{id}\" />
        {name}
    </a>";
    $flowLayout = new WFlowLayout('files');
    $flowLayout->setAdditionalCSSClasses('WFlowLayoutFile');
    foreach($files as $alias => $data){
        $suffix = 'CFile';
        $bigIcon = (file_exists(WIcon::pathFor($suffix, 'mimetype', WIcon::LARGE))) ? $suffix : 'file';
        $output = array('realname'  => htmlentities($data[0], ENT_QUOTES, 'UTF-8'),
            'icon' =>WIcon::pathForMimeIcon($data[2], WIcon::LARGE),
            'linktarget' => '_blank',
            'id' => $alias,
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
echo LGui::endForm();

?>
