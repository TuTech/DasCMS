<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-14
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
class TCmdRuntime
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $request;
    public $data = array();
    
    public function __construct(DOMNode $node)
    {
        $atts = $node->attributes;
        $getNode = $atts->getNamedItem('get');
        if(!$getNode)
        {
            return;
        }
        $this->request = $getNode->nodeValue;
    }
    
    public function setUp(array $environment)
    {
    }
    
    public function run(array $environment)
    {
        $val = '';
        //do what you have to do
        if(array_key_exists($this->request, $environment))
        {
            if(is_array($environment[$this->request]))
            {
                $val = implode(', ', $environment[$this->request]);
            }
            else
            {
                $val = strval($environment[$this->request]);
            }
        }
        return mb_convert_encoding($val,CHARSET,'iso-8859-1,utf-8,auto');
    }
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        $this->data = array($this->request);
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->request = $this->data[0];
        $this->data = array();
    }
}
?>