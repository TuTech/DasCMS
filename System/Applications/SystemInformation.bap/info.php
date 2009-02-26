<?php
if(isset($panel) && $panel->hasWidgets())
{
    echo '<div id="objectInspectorActiveFullBox">';
}
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

$environment_tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'environment');
$environment_tbl->addRow(array('software', 'version'));
$environment_tbl->addRow(array('php', phpversion()));
$environment_tbl->render();

$cache_tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'cache');
$cache_tbl->addRow(array('information', 'value'));
$cache_tbl->addRow(array('size', cacheSize()));
$cache_tbl->render();

$out = array(array(
    'file', 
    'readable', 
    'writeable', 
    'file_permissions',
    'path'
));
pdirlist_r();
if(count($out) == 1)
{
    SNotificationCenter::report('message', 'all_file_permissions_are_ok');
}
else
{
    $fp_table = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'file_permissions');
    $fp_table->setData($out);
    $fp_table->render();
}
echo LGui::verticalSpace();
if(isset($panel) && $panel->hasWidgets())
{
    echo '</div>';
}
?>