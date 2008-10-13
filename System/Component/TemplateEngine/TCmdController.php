<?php
class TCmdController 
    extends 
        BTemplate
    implements 
        ITemplateCommand 
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
            if($child->nodeType != XML_ELEMENT_NODE || strtolower($child->localName) != 'param')
            {
                continue;
            }
            $name = null;
            $value = null;
            foreach ($child->attributes as $att) 
            {
                if($att->name == 'name' || $att->name == 'value')
                {
                    ${$att->name} = $att->value;
                }
            }
            
        	if(!empty($name) && $value !== null)
        	{
        	    $this->parameters[$name] = $value;
        	}
        	elseif($value !== null)
        	{
        	    $this->parameters[] = $value;
        	}
        }
    }
    
    public function setUp(array $environment)
    {
        //do inits n stuff
        if(!empty($this->controller))
        {
            try
            {
                $controllerObject = BObject::InvokeObjectByID($this->controller);
                if(
                    ($controllerObject instanceof ITemplateSupporter)
                    && ($controllerObject->TemplateCallable($this->call))
                   )
                {
                    $this->controllerObject = $controllerObject;
                }
            }
            catch(Exception $e)
            {/*logging might be nice*/
            }
        }
    }
    
    public function run(array $environment)
    {
        //do what you have to do
        if($this->controllerObject != null)
        {
            return $this->controllerObject->TemplateCall($this->call, $this->parameters);
        }
        else
        {
            return '';
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