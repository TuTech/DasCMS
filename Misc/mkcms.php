#!/usr/bin/php
<?php
define('DOCUMENTROOT', '/srv/www/');
define('DOMAINREGEXP', '/^[a-zA-Z0-9-]+\.[a-zA-Z]+$/');
define('STARTDIR', getcwd());


if(!empty($_GET))
{
	exit;
}
if($argc == 1)
{
	die("please specify a domain name\n");
}
if(!preg_match(DOMAINREGEXP,$argv[1]))
{
	die("please specify a VALID domain name\n".
		"regexp is ".DOMAINREGEXP."\n");
}
chdir(DOCUMENTROOT);// /srv/www/
printf("Creating environment for new cms\n");
//check domain 
if(!is_dir($argv[1]))
{
	printf("    Creating domain folder\n");
	mkdir($argv[1]);
}
chdir($argv[1]); // /srv/www/domain.foo/

//version dir
if(!is_dir('version'))
{
	printf("    Creating version folder\n");
	mkdir('version');
}
chdir('version'); // /srv/www/domain.foo/version/
$version = date('ymd');
$i = 1;
while(is_dir(sprintf('%06s-%02d',$version, $i)))
{
	$i++;
}
$currentVersion = sprintf('%06s-%02d',$version, $i);
mkdir($currentVersion);
printf("    Creating version '%s'\n", $currentVersion);
chdir($currentVersion);// /srv/www/domain.foo/version/010203-04/
//loading cms
printf("    Downloading Bambus CMS from  'http://bambus-cms.org/'\n");
passthru('wget http://bambus-cms.org/current', $ret);
if($ret == 0)
{
	//cms download ok
	printf("\n    Extracting Bambus CMS\n");
	passthru('tar -xvjf current', $ret);
}
if($ret == 0)
{
	printf("\n    Extraction completed - please run 'Misc/repair_rights.php' in the cms root dir\n");
	unlink('current');
}

chdir('../..');// /srv/www/domain.foo/
if(!is_link('cms'))
{
	printf("\n    Creating 'cms' symlink\n");
	shell_exec('ln -s version/'.$currentVersion.' cms');
}
chdir(STARTDIR);
echo "\nYou are in ".getcwd()."\n\nDONE\n\n";
?>