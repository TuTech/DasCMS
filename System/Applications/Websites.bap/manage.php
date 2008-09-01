<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
echo LGui::hiddenInput('action', 'delete');
$files = $mp->Index;
ksort($files, SORT_STRING);
$itemTemplate = "<a class=\"listView\" name=\"{id}\" title=\"{title}\" id=\"{id}\" href=\"javascript:selectImage('{id}');\">
    <img src=\"{icon}\" title=\"{title}\" id=\"img_{id}\" alt=\"{bigIcon}\" />
    <input type=\"checkbox\" name=\"select_{id}\" id=\"select_{id}\" />
    {name}
</a>";
$id = 0;
$SCI = SContentIndex::alloc()->init();
$lastchar = '';
foreach($files as $id => $file){
	$fchar = strtoupper(substr($file,0,1));
	if($fchar != $lastchar)
	{
		$lastchar = $fchar;
		printf('<span class="hiddenGroup">%s</span>', $fchar);
	}
   list($dbtitle, $dbalias) = array_values($SCI->getTitleAndAlias(get_class($mp),$id));
	$suffix = 'website';
    $icon = (file_exists(WIcon::pathFor($suffix, 'mimetype'))) ? $suffix : 'file';
    $bigIcon = (file_exists(WIcon::pathFor($suffix, 'mimetype', WIcon::LARGE))) ? $suffix : 'file';
    $output = array('realname'  => htmlentities($file),
        'icon' => 	WIcon::pathFor($icon, 'mimetype'),
        'bigIcon' =>WIcon::pathFor($bigIcon, 'mimetype', WIcon::LARGE),
        'linktarget' => '_blank',
        'id' => $id,
        'link' => '#'.$id,
        'title' => htmlentities($dbalias,ENT_QUOTES, 'utf-8'),
        'name' => wordwrap(htmlentities($file,ENT_QUOTES, 'utf-8'),8,"<wbr />",true),
    );
    $tpl = new WTemplate($itemTemplate, WTemplate::STRING);
    $tpl->setEnvornment($output);
    $tpl->render();
}
echo LGui::endForm();
echo new WScript('hideInputs();');
?>