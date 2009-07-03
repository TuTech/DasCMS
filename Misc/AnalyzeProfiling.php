<?php 
if(empty($_GET['a']))
{
    header('Location: ../');
    exit();
}
$file = basename($_GET['a']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content=" text/html; charset=utf-8" />
		<title>Webseiten - Bambus CMS </title>
		<style type="text/css">
			.runtime{
				width:500px;
				height:60px;
				border: 1px solid #2e3436;
			}
			.function_block{
				height:40px;
				float:left;
				display:block;
				border:1px solid #a40000;
			}
			.function{
				float:left;
				display:block;
				height:20px;
				border: 1px solid #babdb6;
			}
		</style>
	</head>
	<body>
<h1>Profile:</h1>
<?php 
chdir('../Content/logs/');
printf('<h2>%s</h2>',$file);

$dom = new DOMDocument();
$dom->load($file);
$doc = $dom->documentElement;
$xp = new DOMXPath($dom);
printf('<h3>%s</h3><ul><dl>', 'global info');
$c = $xp->query('/profile/info/*');
$duration = 0;
foreach ($c as $cn)
{
    echo '<dt>'.$cn->nodeName.'</dt><dd>'.$cn->nodeValue.'</dd>';
    if($cn->nodeName == 'runTime')
    {
        $duration = floatval($cn->nodeValue);
        echo $duration;
    }
}
printf('</dl></ul>');
printf('<h3>%s</h3><ul><dl>', 'function profilings');
$timings = array();
$timingsum = array();
$sqls = array();
$c = $xp->query('/profile/measurements/measurement');
foreach ($c as $cn)
{
    $fid = null;
    $fsql = null;
    echo '<dt>'.$cn->nodeName.'</dt><dd><dl>';
    
//function info
    
    $fs = $xp->query('info/functionSQL', $cn);
    if($fs->item(0))
    {
        echo '<dt>function SQL</dt><dd>'.$fs->item(0)->nodeValue.'</dd>';
        $fsql = $fs->item(0)->nodeValue;
    }
    $fs = $xp->query('info/functionId', $cn);
    if($fs->item(0))
    {
        $fid = $fs->item(0)->nodeValue;
        $timings[$fid] = isset($timings[$fid]) ? $timings[$fid] : array();
        $timingsum[$fid] = isset($timingsum[$fid]) ? $timingsum[$fid] : 0;
        $sqls[$fid] = $fsql;
        echo '<dt>function ID</dt><dd>'.$fid.'</dd>';
    }

//query info

    
    
//timing
    
    $fs = $xp->query('runTime', $cn);
    $rt = $fs->item(0)->nodeValue;
    if($fid)
    {
        $timings[$fid][] = floatval($rt);
        $timingsum[$fid] += floatval($rt);
    }
    echo '<dt>runTime</dt><dd>'.$rt.'</dd>';
    echo '</dl></dd>';
}
echo '</dl></ul>';
echo '<h2>functions</h2><div class="runtime">';
foreach($timings as $fid => $times)
{
    echo '<span class="function_block" title="'.count($times).' querie(s) in '.$timingsum[$fid].' - '.htmlentities($sqls[$fid]).'">';
    foreach($times as $t)
    {
        $len = round(500/$duration*$t);
        echo "\n\n".'<!-- '.$duration.' '.$t.' '.$len.' -->';
        echo '<div class="function" style="width:'.$len.'px" ></div>';
    }
    echo '</span>';
}
echo '<div>';
?>
</body>
</html>