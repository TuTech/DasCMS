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
$files = DFileSystem::FilesOf(SPath::DOWNLOADS, '/(?!\.php[0-9]?|\.aspx?|\.pl|\.phtml|\.cgi)$/i');

$itemTemplate = "<a name=\"{id}\" title=\"{title}\" id=\"{id}\" href=\"javascript:selectImage('{id}');\">
    <img src=\"{icon}\" title=\"{title}\" id=\"img_{id}\" />
    <input type=\"checkbox\" name=\"select_{id}\" id=\"select_{id}\" />
    {name}
</a>";
$flowLayout = new WFlowLayout('files');
$flowLayout->setAdditionalCSSClasses('WFlowLayoutFile');
foreach($files as $file){
    $id = md5($file);
    $suffix = DFileSystem::suffix($file);
    $bigIcon = (file_exists(WIcon::pathFor($suffix, 'mimetype', WIcon::LARGE))) ? $suffix : 'file';
    $output = array('realname'  => htmlentities($file),
        'icon' =>WIcon::pathFor($bigIcon, 'mimetype', WIcon::LARGE),
        'linktarget' => '_blank',
        'id' => $id,
        'title' => htmlentities(mb_convert_encoding($file,'UTF-8', 'ISO-8859-15,auto'), ENT_QUOTES, 'UTF-8'),
        'name' => mb_convert_encoding(wordwrap($file,16,"<wbr />",true),'UTF-8', 'ISO-8859-15,auto'),
    );
    $tpl = new WTemplate($itemTemplate, WTemplate::STRING);
    $tpl->setEnvironment($output);
    $flowLayout->addItem($tpl);
}
$flowLayout->render();
echo LGui::endForm();
echo new WScript('hideInputs();');
?>
