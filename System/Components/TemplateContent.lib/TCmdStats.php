<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-10
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
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
        return String::htmlEncode(mb_convert_encoding($val,CHARSET,'iso-8859-1,utf-8,auto'));
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
        return sprintf(
			"\n<!-- #Stats#\n%12s%s\n%12s%s\n%12s%s\n%12s%s\n%12s%s\n-->\n",
			'Mem: ',		Core::FileSystem()->formatFileSize(memory_get_usage()),
			'Mem (real): ',	Core::FileSystem()->formatFileSize(memory_get_usage(true)),
			'Mem (peak): ',	Core::FileSystem()->formatFileSize(memory_get_peak_usage(true)),
			'ipadr: ',		RServer::getNumericRemoteAddress(),
			'gentime: ',	round(microtime(true) - CMS_START_TIME, 5).'s'
		);
    }
}
?>