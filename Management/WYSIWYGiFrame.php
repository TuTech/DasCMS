<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
		<base href="<?php
$base = './';
$scrn = getenv('SCRIPT_NAME');
$srvn = getenv('SERVER_NAME');
$prot = getenv('SERVER_PROTOCOL');
if(!empty($prot))
	$prot = strtolower(substr($prot, 0, strpos($prot,'/')));
else
	$prot = 'http';
if(!empty($scrn) && !empty($srvn))
{
	$scrn = dirname($scrn);
	$temp = explode("/", $scrn);
	array_pop($temp);
	for($i = 0; $i < count($temp); $i++)
		if(empty($temp[$i]))unset($temp[$i]);
	$scrn = sprintf('%s://%s/%s/', $prot,$srvn,implode("/", $temp));
	echo $scrn;
}
		?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title></title>
        <link rel="stylesheet" href="./Content/stylesheets/WYSIWYG-Editor.css" type="text/css" media="all" />
        <script language="JavaScript" type="text/javascript">
  			function load(){
  				window.document.designMode = "On";
			}
		</script>
    </head>
    <body contentEditable="true" onload="load();"></body>
</html>