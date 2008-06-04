<?php
$info = array(); //array('size' => 0,'lines' => 0,'chars' => 0,'files' => 0,'folders' => 0,'scripts' => 0);
dirlist_r('./');
$Bambus->FileSystem->returnToRootDir();
?>
<h3><?php echo $Bambus->Translation->sayThis('system');?></h3>
<table cellspacing="0" class="borderedtable full">
<tr>
	<th><?php echo $Bambus->Translation->sayThis('type');?></th>
	<th><?php echo $Bambus->Translation->sayThis('number_of_items');?></th>
	<th><?php echo $Bambus->Translation->sayThis('lines');?></th>
	<th><?php echo $Bambus->Translation->sayThis('size');?></th>
	<th><?php echo $Bambus->Translation->sayThis('chars_per_line');?></th>
</tr>
<?php
foreach(
	array(
		array('folders','',''),
		array('files','','size'),
		array('php-scripts', 'php-lines', 'php-size'), 
		array('js-scripts', 'js-lines', 'js-size'), 
		array('css-scripts', 'css-lines', 'css-size')
		)
	as 
	$linekeys
)
{
	printf(
		'<tr><th class="left_th">%s</th><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>'
		,$Bambus->Translation->sayThis($linekeys[0])								//title
		,empty($info[$linekeys[0]]) ? '' : $info[$linekeys[0]]						//number
		,empty($info[$linekeys[1]]) ? '' : $info[$linekeys[1]]						//lines
		,empty($info[$linekeys[2]]) ? '' : $Bambus->formatSize($info[$linekeys[2]])	//size
		,(!empty($info[$linekeys[2]]) && !empty($info[$linekeys[1]]))				//chars per line
			? round($info[$linekeys[2]]/$info[$linekeys[1]])
			: ''
	);
}

?>
</table>
<br />
<h3><?php echo $Bambus->Translation->sayThis('enviornment');?></h3>
<table cellspacing="0" class="borderedtable full">
<tr>
	<th><?php echo $Bambus->Translation->sayThis('software');?></th>
	<th><?php echo $Bambus->Translation->sayThis('version');?></th>
</tr>
<tr>
	<th class="left_th">PHP</th>
	<td><?php echo phpversion();?></td>
</tr>
</table>
<br />
<h3><?php echo $Bambus->Translation->sayThis('file_permission');?></h3>
<?php
if(!empty($_GET['_action']) && $_GET['_action'] == 'repair_rights')
{
	SNotificationCenter::alloc()->init()->report('message', 'repair_tried');
}
pdirlist_r();
if($out == array())
{
	SNotificationCenter::alloc()->init()->report('message', 'all_file_permissions_are_ok');
}
else
{
	echo $Bambus->Gui->beginTable();
	echo $Bambus->Gui->tableHeader(
		array(
			$Bambus->Translation->sayThis('file'), 
			$Bambus->Translation->sayThis('readable'), 
			$Bambus->Translation->sayThis('writeable'), 
			$Bambus->Translation->sayThis('file_permissions'),
			$Bambus->Translation->sayThis('path')
		)
	);	
	echo implode("\n", $out);
	echo $Bambus->Gui->endTable();
}

?>
<br />