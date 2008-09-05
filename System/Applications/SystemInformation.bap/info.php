<?php
$info = array(); //array('size' => 0,'lines' => 0,'chars' => 0,'files' => 0,'folders' => 0,'scripts' => 0);
dirlist_r('./');
chdir(constant('BAMBUS_CMS_ROOTDIR'));
?>
<h3><?php SLocalization::out('system');?></h3>
<table cellspacing="0" class="borderedtable full">
<tr>
	<th><?php SLocalization::out('type');?></th>
	<th><?php SLocalization::out('number_of_items');?></th>
	<th><?php SLocalization::out('lines');?></th>
	<th><?php SLocalization::out('size');?></th>
	<th><?php SLocalization::out('chars_per_line');?></th>
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
		,SLocalization::get($linekeys[0])								//title
		,empty($info[$linekeys[0]]) ? '' : $info[$linekeys[0]]						//number
		,empty($info[$linekeys[1]]) ? '' : $info[$linekeys[1]]						//lines
		,empty($info[$linekeys[2]]) ? '' : DFileSystem::formatSize($info[$linekeys[2]])	//size
		,(!empty($info[$linekeys[2]]) && !empty($info[$linekeys[1]]))				//chars per line
			? round($info[$linekeys[2]]/$info[$linekeys[1]])
			: ''
	);
}

?>
</table>
<br />
<h3><?php SLocalization::out('environment');?></h3>
<table cellspacing="0" class="borderedtable full">
<tr>
	<th><?php SLocalization::out('software');?></th>
	<th><?php SLocalization::out('version');?></th>
</tr>
<tr>
	<th class="left_th">PHP</th>
	<td><?php echo phpversion();?></td>
</tr>
</table>
<br />
<h3><?php SLocalization::out('file_permission');?></h3>
<?php
if(RURL::get('_action') == 'repair_rights')
{
	SNotificationCenter::report('message', 'repair_tried');
}
pdirlist_r();
if($out == array())
{
	SNotificationCenter::report('message', 'all_file_permissions_are_ok');
}
else
{
	echo LGui::beginTable();
	echo LGui::tableHeader(
		array(
			SLocalization::get('file'), 
			SLocalization::get('readable'), 
			SLocalization::get('writeable'), 
			SLocalization::get('file_permissions'),
			SLocalization::get('path')
		)
	);	
	echo implode("\n", $out);
	echo LGui::endTable();
}

?>
<br />