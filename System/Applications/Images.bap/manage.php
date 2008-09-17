<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @package org.bambus-cms.applications.configuration
 * @since 2006-10-16
 * @version 1.0
 * @author selke@tutech.de
 */
echo LGui::beginForm(array(), 'documentform');
echo LGui::hiddenInput('action', 'delete');
$files = DFileSystem::FilesOf(SPath::IMAGES, '/\.('.implode('|', $allowed).')/i');

$flowLayout = new WFlowLayout('images');
$flowLayout->setAdditionalCSSClasses('WFlowLayoutImage');
$itemTemplate = 
"<a name=\"{id}\" title=\"{title}\" id=\"{id}\" href=\"javascript:selectImage('{id}');\">
    <img src=\"{icon}\" title=\"{title}\" id=\"img_{id}\" />
    <input type=\"checkbox\" name=\"select_{id}\" id=\"select_{id}\" />
    {name}
</a>";
foreach($files as $file){
    $id = md5($file);
    $suffix = DFileSystem::suffix($file);
    $imagePath = ($suffix != 'css' && $suffix != 'gpl')
        ? (SLink::link(array('render' => $file, 'path' => 'image'),'thumbnail.php'))
        : ((file_exists(WIcon::pathFor($suffix, 'mimetype', WIcon::LARGE))) ? $suffix : 'file');
    $output = array(
        'id' => $id,
        'title' => htmlentities($file),
        'name' => mb_convert_encoding(wordwrap($file,16,"<wbr />",true),'UTF-8', 'ISO-8859-15,auto'),
        'icon' =>   $imagePath
    );
    $tpl = new WTemplate($itemTemplate, WTemplate::STRING);
    $tpl->setEnvironment($output);
    $flowLayout->addItem($tpl);
}
$flowLayout->render();
echo LGui::endForm();
echo new WScript('hideInputs();');
?>
