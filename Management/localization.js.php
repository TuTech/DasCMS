<?php 
chdir('..');
require_once 'System/main.php';
header('Content-Type: text/javascript; charset='.CHARSET);
$lang = SLocalization::getCurrentLanguageCode();
echo 'document.write(unescape("%3Cscript%20type%3D%22text%2Fjavascript%22%20src%3D%22',
		'System%2FClientData%2Forg%2Fbambuscms%2Flocalization%2F',
		$lang,'.json?m=',
		filemtime('System/ClientData/org/bambuscms/localization/'.$lang.'.json'),
		'%22%3E%3C%2Fscript%3E"));';

echo 'var _ = function(k){return (org.bambus.localization[k]) ? org.bambus.localization[k] : k.replace(/_/g, " ");};';
?>