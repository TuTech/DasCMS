<?php
chdir(dirname(__FILE__));
require_once '../../System/main.php';
$setup = new SystemSetup();
$setup->run(array(
	'date.timezone'		=> 'Europe/Berlin',
	'date.format'		=> 'd.m.Y H:i:s',

	'system.locale'		=> 'de-DE',
	'system.webmaster'	=> '',

	'website.errors.show' => true,
	'website.errors.mailWebmaster' => false,

	'database.engine'	=> 'MySQL',
	'database.server'	=> '127.0.0.1',
	'database.port'		=> 3306,
	'database.socket'	=> '',
	'database.name'		=> 'DasCMS',
	'database.tablePrefix' => '',
	'database.user'		=> 'root',
	'database.password' => ''
));
?>
