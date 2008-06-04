#!/usr/bin/php
<?php
if(!empty($_GET))
{
	exit;
}
if($argc == 1)
{
	die("please specify a domain name\n");
}
if(is_dir($argv[1])
	 && is_dir($argv[1].'/Content')
	 && is_dir($argv[1].'/Management')
	 && is_dir($argv[1].'/System'))
{
	chdir($argv[1]);
	$file = ("../Bambus-".date('ymd').'.tar.bz2');
	unlink($file);
	passthru("tar -cjhvf ".$file." *");
	printf("\n\nDONE\nBambus-%s.tar.bz2 %10s%5.2fKB\n", date('ymd'),' ', filesize($file)/1024);
}
else
{
	die("please specify a cms path\n".
		"this path must contain the following folders\n".
		"Content, Management & System"
	);
}

?>