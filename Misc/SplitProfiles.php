<?php
header('Content-type: text/css');
chdir('../Content/logs/');
printf("%s\n\n",getcwd());
date_default_timezone_set('Europe/Berlin');

if(!is_dir('csv'))
{
    mkdir('csv');
}

//$doHeader = (!file_exists('csv/script.csv'));
$scriptCSV = fopen('csv/script.csv','w+');
$functionCSV = fopen('csv/function.csv','w+');
//if($doHeader)
{
    fwrite($scriptCSV, "\"item id\";\"address\";\"time\";\"size\";\"run time\";\"mem use\";\"mem peak\";\"measurements\";\"request uri\"\n");//address, time, size, runtime, mem use, mem peak, measurements, request str
    fwrite($functionCSV, "\"item id\";\"function id\";\"query id\";\"mem diff\";\"run time\";\"function\"\n");//address, time, size, runtime, mem use, mem peak, measurements, request str
}
$hdl = opendir('.');
while($item = readdir($hdl))
{
    if(is_file($item) && substr($item,-4) == '.xml')
    {
        try
        {
            $dom = new DOMDocument();
            $dom->load($item);
            fwrite($scriptCSV, get_script_csv($item, $dom));
            fwrite($functionCSV, get_function_measures($item, $dom));
        }
        catch (Exception $e)
        {
            continue;
        }
    }
}

fclose($scriptCSV);
fclose($functionCSV);

function get_script_csv($item, DOMDocument $dom)
{
    preg_match('/^profile-(([\d]+\.){3}[\d]+|localhost)-([\d]{10})([\d]+)\.xml$/', $item, $match);
    list($all, $addr, $nil, $time, $utime) = $match;
    printf("%s @ %s,%s / %s\n", htmlentities($addr), date('Y-m-d H:i:s',$time), $utime, filesize($item));
    
    $xp = new DOMXPath($dom);
    $runtime = floatval(v($xp->query('/profile/info/runTime')));
    $mem = str_replace(',','',substr(v($xp->query('/profile/info/mem')),0,-5));
    $memPeak = str_replace(',','',substr(v($xp->query('/profile/info/memPeak')),0,-5));
    $request = v($xp->query('/profile/info/url'));
    $meas = $xp->query('/profile/measurements/measurement')->length;
    
    return sprintf(
        "\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\"\n"//address, time, size, runtime, mem use, mem peak, measurements, request str
        ,e($item)
        ,e($addr)
        ,e($time.'.'.$utime)
        ,e(filesize($item))
        ,e($runtime)
        ,e($mem)
        ,e($memPeak)
        ,e($meas)
        ,e($request)
    );
}

function get_function_measures($item, DOMDocument $dom)
{
    $xp = new DOMXPath($dom);
    $meas = $xp->query('/profile/measurements/measurement');
    $res = '';
    foreach ($meas as $m)
    {
        $functionId = v($xp->query('info/functionId',$m));
        if(!empty($functionId))
        {
            $queryId = v($xp->query('info/queryId',$m));
            $functionSQL = v($xp->query('info/functionSQL',$m));
            $memDiff = str_replace(',','',substr(v($xp->query('memDiff',$m)),0,-5));
            $runTime = floatval(v($xp->query('runTime',$m)));
            //item id, function id, mem diff, time, sql
            $res = sprintf(
                "\"%s\";\"%s\";\"%s\";\"%s\";\"%s\";\"%s\"\n"
                ,e($item)
                ,e($functionId)
                ,e($queryId)
                ,e($memDiff)
                ,e($runTime)
                ,e($functionSQL)
            );
        }
    }
    return $res;
}
function e($str)
{
    return addslashes($str);
}
function v(DOMNodeList $l)
{
    if($l->item(0))
    {
        return $l->item(0)->nodeValue;
    }
    else
    {
        return '';
    }
}
?>