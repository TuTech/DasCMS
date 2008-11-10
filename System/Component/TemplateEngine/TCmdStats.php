<?php
class TCmdHeader
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
        return sprintf("
<!-- #Stats#
	%12s%10d
	%12s%10d
	%12s%10d
 -->
"
			,'Mem:',memory_get_usage()
			,'Mem (real):',memory_get_usage(true)
			,'Mem (peak):',memory_get_peak_usage(true)
			);
    }
}
?>