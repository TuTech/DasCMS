<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
echo LGui::beginForm(array(), 'documentform');
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
echo LGui::endForm();
echo new WScript('hideInputs();');
?>
