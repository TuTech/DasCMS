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
	printf('<input type="hidden" id="filename" size="30" name="filename" value="%s"/><h2>%s</h2>%s'
    	, htmlentities($File->Title, ENT_QUOTES, 'UTF-8')
    	, htmlentities($File->Title, ENT_QUOTES, 'UTF-8')
    	, $File->Icon
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
        {retainCount}{preview}
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
            'icon' =>WIcon::pathForMimeIcon($Dtype, WIcon::LARGE),
            'preview' => $prev,
            'linktarget' => '_blank',
            'retainCount' => isset($retains[$Did]) ? sprintf('<span class="retainBatch retainBatch-%d">%s</span>', strlen($retains[$Did]), $retains[$Did]) : '',
            'id' => md5($alias),
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
