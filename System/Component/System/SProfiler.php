<?php
class SProfiler extends BSystem implements IShareable 
{
	private $runningProfilings = array();
	private $profilings = array();
	private $myDir = '/tmp/';
	private $startTime = 0;
	private $token = 0;
	private $enabled = false;
	
    public static function profile($file, $line, $desc)
    {
    	$my = self::alloc()->init();
        if(!$my->enabled)
        {
            return 0;
        }
    	$token = ++$my->token;
    	$mem = memory_get_usage();
    	$my->runningProfilings[$token] = array($file, $line, $desc, microtime(true), $mem);
    	return $token;
    }
	
    public static function finish($token)
    {
    	$my = self::alloc()->init();
        if(!$my->enabled || !array_key_exists($token, $my->runningProfilings))
        {
            return;
        }
    	list($file, $line, $desc, $time, $mem) = $my->runningProfilings[$token];
        $time = microtime(true)-$time;
        $mem = memory_get_usage() - $mem;
        $my->profilings[$token] = array($file, $line, $desc, $time, $mem);
        unset($my->runningProfilings[$token]);
    }
    
    public static function cancel($token)
    {
    	$my = self::alloc()->init();
        if(!$my->enabled || (!array_key_exists($token, $my->runningProfilings)))
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
    	$data="\n\n##############".
		    	"\n##Profilings##".
		    	"\n##############".
                "\n#timestamp: ".date('c').
                "\n#url:       ".$_SERVER["REQUEST_URI"].
                "\n#from:      ".$_SERVER["REMOTE_ADDR"]." ".(isset($_SERVER["REMOTE_HOST"]) ? $_SERVER["REMOTE_HOST"] : '').
                "\n#run-time:  ".number_format(((microtime(true) - $this->startTime)*1000),2)."ms".
                "\n#mem-peak:  ".number_format(memory_get_peak_usage())."bytes".
                "\n#mem:       ".number_format(memory_get_usage())."bytes".
                "\n\n";
    	foreach ($this->profilings as $nr => $pfl) 
    	{
    		list($file, $line, $desc, $time, $mem) = $pfl;
            $data .= "##############".
                "\n#nr:       ".$nr.
                "\n#file:     ".$file.
                "\n#line:     ".$line.
                "\n#run-time: ".number_format(($time*1000),2)."ms".
                "\n#mem-diff: ".number_format($mem)."bytes".
                "\n".$desc.
                "\n\n";
    	}
    	$file = $this->myDir.'/Content/logs/profilings.log';
    	$fp = fopen($file, 'a+');
    	fwrite($fp, $data);
    	fclose($fp);
    }
	
	
	//IShareable
    const Class_Name = 'SProfiler';
    public static $sharedInstance = NULL;
    
    /**
     * @return SProfiler
     */
    public static function alloc()
    {
        $class = self::Class_Name;
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