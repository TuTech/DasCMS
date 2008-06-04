<?php
/************************************************
* Bambus CMS 
* Created:     13.03.2007
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH 
* Description: PaletteParser.php
************************************************/
$inColorDefinition = false;
$columns = 4;
$curcol = 0;
$name = 'Palette';
$colors = array();
$gpllines = file('Tango-Palette.gpl');
foreach($gpllines as $line)
{
	$line = trim($line);
	if(!$inColorDefinition)
	{
		$search = strtolower($line);
		if(substr($search, 0, 5) == 'name:')
		{
			$name = trim(substr($line,5));
		}
		elseif(substr($search, 0, 8) == 'columns:')
		{
			$tmp = trim(substr($line,8));
			if(is_numeric($tmp) && $tmp > 0)
				$columns = $tmp;
			//$columns = trim(substr($line,5));
		}
		elseif($search == '#')
		{
			$inColorDefinition = true;
		}
	}
	else
	{
		$parts = explode("\t", $line);
		sscanf($parts[0], "%d %d %d", &$r, &$g, &$b);
		$color = $parts[1];
		$colors[] = array($color, $r, $g, $b);
	}
}
printf("<table border=\"0\" cellspacing=\"5\" style=\"width:200px;background-color:#eeeeec\">");
$rowtemplate = '<tr>';
for($i = 0;$i < $columns;$i++)
{
	$rowtemplate .= "<td>%s</td>";
}
$rowtemplate .= "</tr>\n";
$colorcount = count($colors);
$rows = ceil($colorcount/$columns);
$k = 0;
for($i = 0;$i < $rows;$i++)
{
	echo '<tr>';
	for($y = 0;$y < $columns;$y++)
	{
		if(isset($colors[$k]))
		{
			$color = ($colors[$k][1]+$colors[$k][2]+$colors[$k][3]) < 300 ? 'white' : 'black';
			printf('<td style="overflow:hidden;height:48px;background-color:rgb(%d, %d, %d);font-size:10px;color:%s;">%s <br />#%x%x%x</td>', $colors[$k][1], $colors[$k][2], $colors[$k][3], $color, $colors[$k][0], $colors[$k][1], $colors[$k][2], $colors[$k][3]);
		}
		$k++;
	}
	echo "</tr>\n";
}


printf("</table>");

?>