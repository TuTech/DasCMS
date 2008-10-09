<?php
class TCmdController extends BTemplate 
{
    private $controller;
    private $call;
    private $parameters;
    
    public $data = array();
    
    private $controllerObject = null;
    
    public function __construct(DOMNode $node)
    {
        //suck all information from dom node and save them in public conf array for serialize
        //att guid
        //att call
        //children param
        $atts = $node->attributes;
        $guidNode = $atts->getNamedItem('guid');
        $callNode = $atts->getNamedItem('call');
        if(!$guidNode || !$callNode)
        {
            return;
        }
        $this->controller = $guidNode->nodeValue;
        $this->call = $callNode->nodeValue;
        $this->parameters = array();
        foreach ($node->childNodes as $child) 
        {
            if(strtolower($node->localName) != 'param')
            {
                continue;
            }
        	$name  = $child->attributes->getNamedItem('name');
        	$value = $child->attributes->getNamedItem('value');
        	if($name && $value && $name->nodeValue && $value->nodeValue)
        	{
        	    $this->parameters[$name->nodeValue] = $value->nodeValue;
        	}
        }
    }
    
    public function setUp(array $environment)
    {
        //do inits n stuff
        if($this->controller)
        {
            try
            {
                $controllerObject = BObject::InvokeObjectByID($this->controller);
                if(
                    (!$controllerObject instanceof ITemplateSupporter)
                    && ($controllerObject->TemplateCallable($this->call))
                   )
                {
                    $this->controllerObject = $controllerObject;
                }
            }
            catch(Exception $e)
            {/*logging might be nice*/}
        }
    }
    
    public function run(array $environment)
    {
        //do what you have to do
        if($this->controllerObject != null)
        {
            $this->controllerObject->TemplateCall($this->call, $this->parameters);
        }
    }
    
    public function tearDown()
    {
        $this->controllerObject = null;
    }
    
    public function __sleep()
    {
        $this->data = array();
        $this->data[] = $this->controller;
        $this->data[] = $this->call;
        $this->data[] = $this->parameters;
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->controller = $this->data[0];
        $this->call = $this->data[1];
        $this->parameters = $this->data[2];
        $this->data = array();
    }
}
?>