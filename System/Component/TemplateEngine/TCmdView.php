<?php
class TCmdView
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $for, $show;
    protected static $spores = array();
    public $data = array();
    
    public function __construct(DOMNode $node)
    {
        $atts = $node->attributes;
        $for = $atts->getNamedItem('for');
        $show = $atts->getNamedItem('show');
        if(!$getNode || !$show)
        {
            return;
        }
        $this->for = $for->nodeValue;
        $this->show = $show->nodeValue;
    }
    
    public function setUp(array $environment)
    {
        if($this->for != null 
            && !array_key_exists($this->for, self::$spores) 
            && QSpore::exists($this->for) 
            && QSpore::isActive($this->for))
        {
            self::$spores[$this->for] = QSpore::byName($this->for);
        }
    }
    
    public function run(array $environment)
    {
        if(array_key_exists($this->for, self::$spores))
        {
            return self::$spores[$this->for]->TemplateGet($this->show);
        }
        else return '';
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