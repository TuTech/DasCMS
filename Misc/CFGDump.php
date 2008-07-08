#!/usr/bin/php
<?php
$data = file('../Content/configuration/system.php');
unset($data[0]);
$stream = implode($data);
$cfgarr = unserialize($stream);
foreach ($cfgarr as $key => $value) 
{
	$dat = @unserialize($value);
	if($dat && is_array($dat))
	{
		printf("%20s:\n",$key);
		$sp = '';
		foreach ($dat as $vkey => $vval) 
		{
			if(is_array($vval))
			{
				printf("%20s%20s:\n",$sp,$vkey);
				foreach ($vval as $vvsk => $vvv) 
				{
					printf("%40s%20s:%s\n",$sp,$vvsk,$vvv);
				}
			}
			else
			{
				printf("%20s%20s:%s\n",$sp,$vkey,$vval);
			}
		}
		
	}
	else
	{
		printf("%20s:%s\n",$key,$value);
	}
}

?>