<?php
$info = array(); //array('size' => 0,'lines' => 0,'chars' => 0,'files' => 0,'folders' => 0,'scripts' => 0);
dirlist_r('./');
chdir(constant('BAMBUS_CMS_ROOTDIR'));
$system_tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'system');
$system_tbl->addRow(array('type', 'number_of_items', 'lines', 'size', 'chars_per_line'));
$system_tbl->setHeaderTranslation(true);
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
    $system_tbl->addRow(array(
        $linekeys[0]
        ,empty($info[$linekeys[0]]) ? '' : $info[$linekeys[0]]                      //number
        ,empty($info[$linekeys[1]]) ? '' : $info[$linekeys[1]]                      //lines
        ,empty($info[$linekeys[2]]) ? '' : DFileSystem::formatSize($info[$linekeys[2]]) //size
        ,(!empty($info[$linekeys[2]]) && !empty($info[$linekeys[1]]))               //chars per line
            ? round($info[$linekeys[2]]/$info[$linekeys[1]])
            : ''
    ));
}
$system_tbl->render();
echo LGui::verticalSpace();

$environment_tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'environment');
$environment_tbl->addRow(array('software', 'version'));
$environment_tbl->addRow(array('php', phpversion()));
$environment_tbl->render();
echo LGui::verticalSpace();

$cache_tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'cache');
$cache_tbl->addRow(array('information', 'value'));
$cache_tbl->addRow(array('size', cacheSize()));
$cache_tbl->render();
echo LGui::verticalSpace();


?>
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