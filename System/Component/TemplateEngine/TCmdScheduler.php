<?php
class TCmdScheduler
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $int = 60000; 
    public $data;
    public function __construct(DOMNode $node)
    {
        $atts = $node->attributes;
        $int = $atts->getNamedItem('interval');
        if(!empty($int))
        {
            $this->int = intval($int);
        }
        
    }
    
    public function setUp(array $environment)
    {
    }
    
    public function run(array $environment)
    {
        return $this;
    }
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        $this->data = $this->int;
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->int = $this->data;
        $this->data = null;
    }
    
    public function __toString()
    {
        return sprintf(
        	"<img src=\"scheduler.php\" alt=\"scheduler\" id=\"bcms-scheduler\"/>".
        	"<script type=\"text/javascript\">".
        	"var i = 1;org.bambuscms.scheduler = function(){document.getElementById(\"bcms-scheduler\").src = \"scheduler.php?\"+(i++);}".
        	"window.setInterval(\"org.bambuscms.scheduler()\", %d);</script>", $this->int);
    }
}
?>