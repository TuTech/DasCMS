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



function getDataFromSVNXML($xml){
	SErrorAndExceptionHandler::muteErrors();
	$ret = array('r' => '', 'd' => '');

	$doc = new DOMDocument('1.0', 'utf-8');
	$doc->loadXML($xml);
	$xpath = new DOMXpath($doc);

	$rev = $xpath->query('/info/entry/commit/@revision');
	if($rev && $rev->length == 1){
		$ret['r'] = $rev->item(0)->nodeValue;
	}

	$date = $xpath->query('/info/entry/commit/date');
	if($date && $date->length == 1){
		$ret['d'] = $date->item(0)->nodeValue;
	}


	SErrorAndExceptionHandler::reportErrors();
	return $ret['r'].($ret['d'] == '' ? '' : ' ('.date('r', strtotime($ret['d'])).')');
}

$hasSVN = -1;
$output = array();
exec('svn --version', $output, $hasSVN);
if($hasSVN == 0){
	$svn_tbl = new WTable(WTable::HEADING_TOP|WTable::HEADING_LEFT, 'version_control');
	$svn_tbl->addRow(array('information', 'value'));
	$svn_tbl->addRow(array('version_control_software', count($output) ? $output[0] : 'unknown'));

	$curInfo = array();
	$srvInfo = array();
	exec('svn info --xml', $curInfo, $curInfoOK);
	exec('svn info --xml -r HEAD', $srvInfo, $srvInfoOK);
	var_dump($srvInfo);
	if($curInfoOK == 0){
		$curXML = implode('', $curInfo);
		$svn_tbl->addRow(array('installed_version', getDataFromSVNXML($curXML)));
	}
	if($curInfoOK == 0 && $srvInfoOK == 0){
		$srvXML = implode('', $srvInfo);
		$svn_tbl->addRow(array('latest_version',  getDataFromSVNXML($srvXML)));
	}
	$svn_tbl->render();
}

echo "<br />";
?>