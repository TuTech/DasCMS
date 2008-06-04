<pre><?php
chdir("/home/lse/public_html");
$start = microtime(true);
$items = 0; 
function rdir($dir, &$items)
{
	if($hdl = @opendir($dir))
	{
		while($itemName = @readdir($hdl))
		{
			$item = $dir.$itemName;
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
				rdir($item.'/', $items);
			}
		}
	}
}

rdir('./', $items);
$end = microtime(true);
echo "\n\n", 
$end-$start, "sec\n",
$items,"items\n",
memory_get_peak_usage(true),"bytes mem at peak\n";
?></pre>