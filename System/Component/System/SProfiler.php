<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-07-30
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SProfiler 
    extends 
        BSystem 
    implements 
        IShareable 
{
	private $runningProfilings = array();
	private $profilings = array();
	private $myDir = '/tmp/';
	private $startTime = 0;
	private $token = 0;
	private $enabled = false;
	
    public static function profile($file, $line, $desc, array $info = array())
    {
    	$my = self::alloc()->init();
        if(!$my->enabled)
        {
            return 0;
        }
    	$token = ++$my->token;
    	$mem = memory_get_usage();
    	$my->runningProfilings[$token] = array($file, $line, $desc, microtime(true), $mem, $info);
    	return $token;
    }
	
    public static function finish($token)
    {
    	$my = self::alloc()->init();
        if(!$my->enabled || !array_key_exists($token, $my->runningProfilings))
        {
            return;
        }
    	list($file, $line, $desc, $time, $mem, $info) = $my->runningProfilings[$token];
        $time = microtime(true)-$time;
        $mem = memory_get_usage() - $mem;
        $my->profilings[$token] = array($file, $line, $desc, $time, $mem, $info);
        unset($my->runningProfilings[$token]);
    }
    
    public static function cancel($token)
    {
    	$my = self::alloc()->init();
        if(!$my->enabled || (!array_key_exists($token, $my->runningProfilings)))
        {
            return;
        }
    	unset($my->runningProfilings[$token]);
    }

    public function __construct()
    {
    	$this->myDir = getcwd();
    	$this->startTime = microtime(true);
    	$this->enabled = file_exists($this->myDir.'/Content/logs/profilings.log');
    }
    
    public function __destruct()
    {
    	if(!$this->enabled)
    	{
    		return;
    	}
    	ksort($this->profilings);
    	global $_SERVER;
    	$xml = new SSimpleXMLWriter('utf-8','1.0',true);
    	$xml->openTag('profile');
    	$xml->openTag('info');
    	$xml->tag('timestamp',array(),date('c'));
    	$xml->tag('url',array(),$_SERVER["REQUEST_URI"]);
    	$xml->tag('from',array(),$_SERVER["REMOTE_ADDR"]." ".(isset($_SERVER["REMOTE_HOST"]) ? $_SERVER["REMOTE_HOST"] : ''));
    	$xml->tag('runTime',array(),number_format(((microtime(true) - $this->startTime)*1000),2)."ms");
    	$xml->tag('memPeak',array(),number_format(memory_get_peak_usage())."bytes");
    	$xml->tag('mem',array(),number_format(memory_get_usage())."bytes");
    	$xml->closeTag();
    	$xml->openTag('measurements');
    	foreach ($this->profilings as $nr => $pfl) 
    	{
    		list($file, $line, $desc, $time, $mem, $info) = $pfl;
    		$xml->openTag('measurement',array('nr' => $nr, 'file' => $file, 'line' => $line));
    		$xml->tag('runTime',array(),number_format(($time*1000),2)."ms");
            $xml->tag('memDiff',array(),number_format($mem)."bytes");
            $xml->tag('desc',array(),$desc,true);
            $xml->openTag('info',array());
            foreach($info as $tagName => $data)
            {
                 $xml->tag($tagName,array(),$data,true);
            }
            $xml->closeTag();
            $xml->closeTag();
    	}
    	$xml->closeAll();
    	$file = sprintf('%s/Content/logs/profile-%s-%s.xml',$this->myDir,$_SERVER["REMOTE_ADDR"],$this->startTime*100);
    	$fp = fopen($file, 'w+');
    	fwrite($fp, strval($xml));
    	fclose($fp);
    }
	
	
	//IShareable
    const CLASS_NAME = 'SProfiler';
    public static $sharedInstance = NULL;
    
    /**
     * @return SProfiler
     */
    public static function alloc()
    {
        $class = self::CLASS_NAME;
        if(self::$sharedInstance == NULL && $class != NULL)
        {
            self::$sharedInstance = new $class();
        }
        return self::$sharedInstance;
    }
    
    /**
     * @return SProfiler
     */
    function init()
    {
        return $this;
    }
    //end IShareable
}
?>