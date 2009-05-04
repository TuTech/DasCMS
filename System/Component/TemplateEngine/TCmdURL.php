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
class TCmdURL
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
        $this->val = strval(RURL::get($this->request, 'UTF-8'));
    }
    
    public function run(array $environment)
    {
        return $this->val;
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