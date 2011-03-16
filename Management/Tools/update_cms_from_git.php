<?php
/**
 * update cms from git server
 */
chdir(dirname(__FILE__));

function log($str){
	printf("%s: %s\n", date('Y-m-d H:i:s'), $str);
}
function section($name){
	printf("\n===[%s]===\n", $name);
}
function getApacheUserAndGroup(){
	//FIXME load data from cms config and ask here if not present
	return 'wwwrun:www';//SuSE defaults
}

section('GIT');
	log('Pulling from Git Server');
		system('git pull');

section('Building CMS caches');
	log('Building sql queries');
		system('php -f buildSQL.php');
	log('Building class cache');
		system('php -f buildIndex.php');
	log('Building cache-manifest');
		system('php -f buildCacheManifest.php');

section('Setting permissions');
	log('Group and owner');
		system('chown -R '.getApacheUserAndGroup().' ../../');
	log('Dir permissions');
		system('find ../../ -type d ! -perm 755 -print0 | xargs -0 chmod 755');
	log('File permissions');
		system('find ../../ -type f ! -perm 444 -print0 | xargs -0 chmod 444');
		system('find ../../Content/ -type f ! -perm 644 -print0 | xargs -0 chmod 644');

section('DONE');
?>