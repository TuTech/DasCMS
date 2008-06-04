#! /usr/bin/php
<?php
function mkdir_recursive($pathname, $mode = 0777)
{
    is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname), $mode);
    return is_dir($pathname) || @mkdir($pathname, $mode);
}

$gifColors = 255;

if(!empty($argv[1]) && is_numeric($argv[1])) $gifColors =  $argv[1];

$force = in_array('-f', $argv);

chdir(dirname(__FILE__));
chdir('./scalable/');
$h = opendir('.');
$conv = 0;
$skipped = 0;
$created = 0;

echo "\nBUILDING BAMBUS ICONS\n---------------------\n";
if($force)
{
	echo "\nCOMPLETE REBUILD\n\n";
}
while($dir = readdir($h))
{
	if(!is_dir($dir) || substr($dir,0,1) == '.')continue;
	chdir($dir);
	$hdl = opendir('.');
	$items = array();
	$paths = array();
	while($item = readdir($hdl))
	{
		if(substr($item,0,1) == '.' || is_dir($item) || substr($item,-3) != 'svg')continue;
		if(substr($item,0,5) == 'large')
		{
			$path = '../../48x48/'.$dir.'/'.substr($item,6,-4).'.png';
		}
		elseif(substr($item,0,6) == 'medium')
		{
			$path = '../../32x32/'.$dir.'/'.substr($item,7,-4).'.png';
		}
		elseif(substr($item,0,5) == 'small')
		{
			$path = '../../22x22/'.$dir.'/'.substr($item,6,-4).'.png';
		}
		elseif(substr($item,0,5) == 'extra')
		{
			$path = '../../16x16/'.$dir.'/'.substr($item,12,-4).'.png';
		}
		else
		{
			$skipped++;
			continue;		
		}
		if(!is_dir(dirname($path)))
			mkdir_recursive(dirname($path));
		$items[] = $item;
		$paths[] = $path;

		$conv++;
	}
	closedir($hdl);
	if(count($items) > 0)
	{
		$perc = (50/count($items));
		//echo "converting icons for ".$dir."\n";
		for($i = 0;$i < count($items); $i++)
		{
			printf("%-10s [",substr($dir,0,10));
			$num = $perc*($i+1);
			for($y = 0; $y <= 49;$y++)
			{
				if($num >=$y) echo '=';
				elseif($num >=($y-1) && $num < $y) echo '>';
				else echo ' ';
			}
			printf("] (%d/%d)\r", $i+1, count($items));
			if($force || !file_exists($paths[$i]) || filemtime($items[$i]) != filemtime($paths[$i]))
			{
				$command = "/usr/bin/inkscape -zf ".escapeshellarg($items[$i])." -e ".escapeshellarg($paths[$i]).";";
				exec($command);
				@touch($paths[$i], filemtime($items[$i]));
				$created++;
			}
			if($force || !file_exists(substr($paths[$i],0,-4).'-gray.png') || filemtime($items[$i]) != filemtime(substr($paths[$i],0,-4).'-gray.png'))
			{
				//$command = "/usr/bin/convert ".escapeshellarg($paths[$i])." -type Grayscale  ".escapeshellarg(substr($paths[$i],0,-4).'-gray.png').";";
				$command = "/usr/bin/convert ".escapeshellarg($paths[$i])." -colorspace Gray  ".escapeshellarg(substr($paths[$i],0,-4).'-gray.png').";";
				exec($command);
				@touch(substr($paths[$i],0,-4).'-gray.png', filemtime($items[$i]));
				$created++;
			}
/*no more gifs
			if($force || !file_exists(substr($paths[$i],0,-3).'gif') || filemtime($items[$i]) != filemtime(substr($paths[$i],0,-3).'gif'))
			{
				$command = "/usr/bin/convert ".escapeshellarg($paths[$i])." -colors ".$gifColors." -antialias ".escapeshellarg(substr($paths[$i],0,-3).'gif').";";
				exec($command);
				@touch(substr($paths[$i],0,-3).'gif', filemtime($items[$i]));
				$created++;
			}
			if($force || !file_exists(substr($paths[$i],0,-4).'-gray.gif') || filemtime($items[$i]) != filemtime(substr($paths[$i],0,-4).'-gray.gif'))
			{
				$command = "/usr/bin/convert ".escapeshellarg($paths[$i])." -type Grayscale  ".escapeshellarg(substr($paths[$i],0,-4).'-gray.gif').";";
				exec($command);
				@touch(substr($paths[$i],0,-4).'-gray.gif', filemtime($items[$i]));
				$created++;
			}*/
		}
		echo "\n";
	}
	chdir('..');
}
printf("%d files / %d created / %d skipped\n", $conv, $created, $skipped);
echo "\07";
for($i = 3; $i >= 0;$i--)
{
	echo sprintf("\r%3d",$i);
	sleep(1);
	if($i == 0) echo "\07";
}
echo "\n";
?>
