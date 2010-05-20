<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//path: $file = 'System/Resource/Translation/'.$language.'.strings';
require_once '../../System/main.php';

$path = 'System/Resource/Translation/';
$items = scandir($path);
foreach ($items as $item){
	if(is_file($path.$item) && substr($item,0,1) != '.' && substr($item,-8) == '.strings'){
		$lang = substr($item,0,-8);
		echo $lang."\n";
		$translations = array();
		$data = file($path.$item);
		foreach ($data as $line)
		{
			$tab = mb_strpos($line, "\t");
			if($tab > 0 && mb_strlen($line) > ($tab+2))
			{
				$key = mb_substr($line, 0, $tab);
				$value = mb_substr($line, $tab+1, -1);
				$translations[$key] = $value;
			}
		}

		$jsData = 'org.bambuscms.localization = '.json_encode($translations).';';
		$outPath = sprintf('System/ClientData/org/bambuscms/localization/%s.json', $lang);
		Core::dataToFile($jsData, $outPath);
	}
}
$jsData = 'org.bambuscms.localization = {};';
$outPath = sprintf('System/ClientData/org/bambuscms/localization/nu_LL.json');
Core::dataToFile($jsData, $outPath);
?>
