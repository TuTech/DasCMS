<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH 
 * @author selke@tutech.de
 * @package org.bambuscms.applications.systeminformation
 * @since 2006-10-16
 * @version 1.0
 */
$controller = SApplication::appController();
$info = array(); 
$controller->dirlist_r('./');
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
$cache_tbl->addRow(array('size', $controller->cacheSize()));
$cache_tbl->render();

echo "<br />";
?>