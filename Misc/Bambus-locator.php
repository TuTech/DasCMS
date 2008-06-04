#!/usr/bin/php
<?php
$directories = array();
$files = array();

$out = array();
$max = 0;
$installationsfound = 0;
function createIndex($dir = './')
{
	global $out, $max, $installationsfound;
	$dirsToIndex = array();
	$max = max($max, strlen($dir));
	
	//printf(" searching '%-{$max}s'\r", $dir);
	//usleep(1);
	if($hdl = opendir($dir))
	{
		while($itemName = readdir($hdl))
		{
			$item = $dir.$itemName;
			if($itemName == '.' || $itemName == '..')
			{
				continue;
			}
			if(is_dir($item) && substr($itemName,0,1) != '.')
			{
				$dirsToIndex[] = $item.'/';
			}
			elseif((is_file($item) || is_link($item)) && strtolower($itemName) == 'bambus.php')
			{
				$data = file($item);
				$found = false;
				$installationsfound++;
				foreach($data as $line)
				{
					if(preg_match('/define[\s]?\([\s\'\"]+BAMBUS_VERSION[\s\'\"]+,[\s\'\"]+(.*)[\s\'\"]+\)/', $line, $matches))
					{
						if(!empty($matches[1]))
						{
							$temp = str_replace('-', '.', $matches[1]);
							$temp = str_replace('/', '.', $temp);
							$parts = explode('.', $temp);
							
							$versionNumber = sprintf('%1d.%02d.%02d-(%s)', array_shift($parts), array_shift($parts), array_shift($parts),$matches[1]);
							$out[realpath($item)] = $versionNumber;//$matches[1];
							$found = true;
						}
						break;
					}
				}
				if(!$found)
				{
					foreach($data as $line)
					{
						if(strpos(strtolower($line),'define') !== false
							&&strpos(strtolower($line),'defined') === false
							&& strpos($line,'BAMBUS_VERSION') !== false
						)
						{
							$out[realpath($item)] = $line;
							break;
						}
					}	
				}
			}
		}
		closedir($hdl);
	}
	foreach ($dirsToIndex as $dirti) 
	{
		createIndex($dirti);
	}
	
}
echo "Looking for Bambus\n";

createIndex();
asort($out);
foreach ($out as $path => $version) 
{
	printf("%-32s\t%s\n", $version, $path);
}

echo "\n\n".$installationsfound." Bambus installations found\n";
//var_dump($directories);
//var_dump($files);


?>