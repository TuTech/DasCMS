<?php
$TPL = array(
        'calls' => 0,
        'avgTime' => 0,
        'sumTime' => 0
    );
$summary = array();
//header('Content-type: text/css');
chdir('../Content/logs/csv');
date_default_timezone_set('Europe/Berlin');
?>
<h3>times are in milliseconds</h3>
<table>
	<tr>
		<td>
			<h2><a href="?sortBy=calls">calls</a></h2>
		</td>
		<td><h2>&nbsp;/&nbsp;</h2></td>
		<td>
			<h2><a href="?sortBy=avgTime">avgTime</a></h2>
		</td>
		<td><h2>&nbsp;/&nbsp;</h2></td>
		<td>
			<h2><a href="?sortBy=sumTime">sumTime</a></h2>
		</td>
	</tr>
</table>
<?php 
$fp = fopen('function.csv', 'r');
$header = fgetcsv($fp,4096,';','"');//title
while($row = fgetcsv($fp,4096,';','"'))
{
    $data = array();
    foreach($header as $i => $head)
    {
        //printf("%20s: %s\n", $head, $row[$i]);
        $data[$head] = $row[$i];
    }
    //echo "\n\n";
    foreach(array('Average', $data['item id']) as $target)
    {
        if(!isset($summary[$target]))
        {
            $summary[$target] = array();
        }        
        foreach(array('function id', 'query id') as $tosum)
        {
            if(!isset($summary[$target][$tosum]))
            {
                $summary[$target][$tosum] = array();
            }
            if(!isset($summary[$target][$tosum][$data[$tosum]]))
            {
                $summary[$target][$tosum][$data[$tosum]] = $TPL;
                $summary[$target][$tosum][$data[$tosum]]['SQL'] = $data['function'];
            }
            $time  = $data['run time'];
            $sumTime = $summary[$target][$tosum][$data[$tosum]]['calls']*$summary[$target][$tosum][$data[$tosum]]['avgTime']+$time;
            $summary[$target][$tosum][$data[$tosum]]['calls']++;
            $summary[$target][$tosum][$data[$tosum]]['avgTime'] = $sumTime/$summary[$target][$tosum][$data[$tosum]]['calls'];
            $summary[$target][$tosum][$data[$tosum]]['sumTime'] = $sumTime;
        }
    }
} 
fclose($fp);

$sortBy = (isset($_GET['sortBy'])) ? $_GET['sortBy'] : 'avgTime';

foreach(array('Average', $data['item id']) as $target)
{
    foreach(array('function id', 'query id') as $tosum)
    {
        $sh = array();
        $bak = array();
        foreach($summary[$target][$tosum] as $id => $data)
        {
            $sh[$id] = $data[$sortBy];
            $bak[$id] = $data;
        }
        asort($sh);
        $sh = array_reverse($sh);
        $summary[$target][$tosum] = array();
        foreach($sh as $id => $rubbish)
        {
            $summary[$target][$tosum][$id] = $bak[$id];
        }
    }
}

function list_r($data)
{
    if(is_array($data))
    {
        echo '<table border="1">';
        foreach($data as $t => $v)
        {
            printf('<tr valign="top"><th>%s</th><td>', $t);
            list_r($v);
            echo '</td></tr>';
        }
        echo '</table>';
    }
    else
    {
        echo $data;
    }
}
list_r($summary);
?>