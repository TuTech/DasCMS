<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-02-06
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
class TCmdText
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $val = '';
    public $data = array();
    
    public function __construct(DOMNode $node)
    {
        $atts = $node->attributes;
        $getNode = $atts->getNamedItem('value');
        if(!$getNode)
        {
            return;
        }
        $this->val = $getNode->nodeValue;
    }
    
    public function setUp(array $environment)
    {
    }
    
    public function run(array $environment)
    {
        return mb_convert_encoding($this->val,CHARSET,'iso-8859-1,utf-8,auto');
    }
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        $this->data = array($this->val);
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->val = $this->data[0];
        $this->data = array();
    }
}
?>