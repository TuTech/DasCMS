<?php
/************************************************
* Bambus CMS 
* Created:     16. Okt 06
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: 
************************************************/
if(!class_exists("Bambus"))die('No login? No bambus for you, hungry Panda!');
echo $Bambus->Gui->beginForm(array(), 'documentform');
echo $Bambus->Gui->hiddenInput('action', 'delete');
$files = array();
$sorthelp = array();
$index = $Bambus->Pages->Index;
foreach($index as $id => $name)
{
	$sorthelp[strtoupper($name).$id] = $id;
}
ksort($files, SORT_STRING);
foreach($sorthelp as $id)
{
	$files[$id] = $documents[$id];
}
$itemTemplate = 
"<a class=\"listView\" name=\"{id}\" title=\"{title}\" id=\"{id}\" href=\"javascript:selectImage('{id}');\">
    <img src=\"{icon}\" title=\"{title}\" class=\"{type}\" id=\"img_{id}\" alt=\"{bigIcon}\" />
    <input type=\"checkbox\" name=\"select_{id}\" id=\"select_{id}\" />
    {name}
</a>";
$id = 0;
$lastchar = '';
foreach($files as $id => $name)
{
	$Page = $Bambus->Pages->open($id, true);
	$fchar = strtoupper(substr($Page->Title_ISO,0,1));
	if($fchar != $lastchar)
	{
		$lastchar = $fchar;
		printf('<span class="hiddenGroup">%s</span>', htmlentities($fchar));
	}
    $suffix = strtolower($Page->Type);
    $icon = ($suffix == 'php') 
    	? 'application-php' 
    	: (($suffix == 'html') 
    		? 'document' 
    		: 'text');
    $output = array(
		'realname'  => htmlspecialchars($Page->Title),
        'bigIcon' => 	$Bambus->Gui->iconPath($icon, '', 'mimetype','large'),
        'icon' => $Bambus->Gui->iconPath($icon, '', 'mimetype','small'),
        'linktarget' => '_blank',
        'id' => $id,
        'link' => '#'.$id,
        'title' => htmlspecialchars($Page->Title),
        'name' => str_replace(chr(11), ' ', wordwrap(str_replace(' ', chr(11), $Page->Title.'.'.$suffix),12,"<wbr />",true)),
    );
    echo $Bambus->Template->parse($itemTemplate, $output, 'string');
}
echo $Bambus->Gui->endForm();
echo $Bambus->Gui->script('hideInputs();');
?>
