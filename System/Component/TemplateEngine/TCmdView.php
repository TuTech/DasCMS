<?php
class TCmdView
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $for, $show;
    public $data = array();
    
    private $res = '';
    
    public function __construct(DOMNode $node)
    {
        $atts = $node->attributes;
        $for = $atts->getNamedItem('for');
        $show = $atts->getNamedItem('show');
        if(!$for || !$show)
        {
            return;
        }
        $this->for = $for->nodeValue;
        $this->show = $show->nodeValue;
    }
    
    public function setUp(array $environment)
    {
        $v = new VSporeHelper();
        if($v->TemplateCallable($this->show))
        {
            $this->res = $v->TemplateCall($this->show, array('view' => $this->for));
        }
    }
    
    public function run(array $environment)
    {
        return $this->res;
    }
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        $this->data = array($this->for, $this->show);
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->for = $this->data[0];
        $this->show = $this->data[1];
        $this->data = array();
    }
}
?>