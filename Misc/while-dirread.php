<pre><?php
chdir("/home/lse/public_html");
$start = microtime(true);
$dirsToRead = array('.');
$items = 0;
while($currentDir = array_shift($dirsToRead))
{
	if($dirHandle = @opendir($currentDir))
	{
		while($itemName = @readdir($dirHandle))
		{
			$item = $currentDir.'/'.$itemName;
			if($itemName == '.' || $itemName == '..')
			{
				continue;
			}
			$items++;
			if($items%1000 == 0) 
			{
				printf(" %8d %16sbytes mem%32s\r", $items, memory_get_usage(true),'');
			}
			if(is_dir($item))
			{
				array_push($dirsToRead, $item);
			}
		}
	}
}
$end = microtime(true);
echo "\n\n", 
$end-$start, "sec\n",
$items,"items\n",
memory_get_peak_usage(true),"bytes mem at peak\n";
?></pre>