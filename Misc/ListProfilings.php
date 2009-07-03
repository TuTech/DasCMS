<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content=" text/html; charset=utf-8" />
		<title>Webseiten - Bambus CMS </title>
	</head>
	<body>
<h1>Profilings:</h1>
<?php 
chdir('../Content/logs/');
printf('<h2>%s</h2>',getcwd());
?>
<ol>
<?php
date_default_timezone_set('Europe/Berlin');
$hdl = opendir('.');
while($item = readdir($hdl))
{
    if(is_file($item) && substr($item,-4) == '.xml')
    {
        preg_match('/^profile-(([\d]+\.){3}[\d]+|localhost)-([\d]{10})([\d]+)\.xml$/', $item, $match);
        list($all, $addr, $nil, $time, $utime) = $match;
        printf("<li><a href=\"AnalyzeProfiling.php?a=%s\">%s @ %s,%s / %s</a></li>\n", urlencode($item), htmlentities($addr), date('Y-m-d H:i:s',$time), $utime, filesize($item));
    }
}
?>
</ol>
</body>
</html>