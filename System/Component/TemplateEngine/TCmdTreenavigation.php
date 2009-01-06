<?php
class TCmdTreenavigation
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $show;
    public $data = array();
    
    public function __construct(DOMNode $node)
    {
        $atts = $node->attributes;
        $show = $atts->getNamedItem('show');
        if(!$show)
        {
            return;
        }
        $this->show = $show->nodeValue;
    }
    
    public function setUp(array $environment)
    {
    }
    
    public function run(array $environment)
    {
        $res = '';
        $n = new NTreeNavigation();
        if($n->TemplateCallable('embed'))
        {
            $res = $n->TemplateCall('embed', array('name' => $this->show));
        }
        return $res;
    }
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        $this->data = array($this->show);
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->show = $this->data[0];
        $this->data = array();
    }
}
?>