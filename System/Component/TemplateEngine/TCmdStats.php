<?php
class TCmdStats
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $request;
    private $val;
    public $data = array();
    
    public function __construct(DOMNode $node)
    {
    }
    
    public function setUp(array $environment)
    {
    }
    
    public function run(array $environment)
    {
        return $this;
    }
    
    private function encode($val)
    {
        return htmlentities(mb_convert_encoding($val,'UTF-8','iso-8859-1,utf-8,auto'), ENT_QUOTES, 'UTF-8');
    }
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        //$this->data = array($this->request);
        return array();//'data'
    }
    
    public function __wakeup()
    {
        //$this->request = $this->data[0];
        $this->data = array();
    }
    
    public function __toString()
    {
        global $_SERVER;
        list($a, $b, $c, $d) = explode('.', $_SERVER['REMOTE_ADDR']);
        
        $num = (sprintf('0x%02x%02x%02x%02x',$a, $b, $c, $d));
        return sprintf("
<!-- #Stats#
	%12s%s
	%12s%s
	%12s%s
	%12s%s
 -->
"
			,'Mem: ',DFileSystem::formatSize(memory_get_usage())
			,'Mem (real): ',DFileSystem::formatSize(memory_get_usage(true))
			,'Mem (peak): ',DFileSystem::formatSize(memory_get_peak_usage(true))
			,'ipnum: ', $num
			);
    }
}
?>