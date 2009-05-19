<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-06
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
class TCmdView
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $for, $show;
    public $data = array();
    private $width = null, $height = null, $scale = null, $color = null, $fixcontent = null;
    private $res = '';
    
    public function __construct(DOMNode $node)
    {
        $atts = $node->attributes;
        $for = $atts->getNamedItem('for');
        $show = $atts->getNamedItem('show');
        $width = $atts->getNamedItem('width');
        $height = $atts->getNamedItem('height');
        $scale = $atts->getNamedItem('scale');
        $color = $atts->getNamedItem('color');
        $fixcontent = $atts->getNamedItem('fixcontent');
        if(!$for || !$show)
        {
            return;
        }
        $this->for = $for->nodeValue;
        $this->show = $show->nodeValue;
        if($width)$this->width = $width->nodeValue;
        if($height)$this->height = $height->nodeValue;
        if($scale)$this->scale = $scale->nodeValue;
        if($color)$this->color = $color->nodeValue;
        if($fixcontent)$this->fixcontent = $fixcontent->nodeValue;
    }
    
    public function setUp(array $environment)
    {
        $v = new VSporeHelper();
        if($this->fixcontent)
        {
            $v->TemplateCall('altercontent', array(
            	'view' => $this->for,
                'alias' => $this->fixcontent
            ));
        }
        if($v->TemplateCallable($this->show))
        {
            $this->res = $v->TemplateCall($this->show, array(
            	'view' => $this->for,
                'width' => $this->width,
                'height' => $this->height,
                'scale' => $this->scale,
                'color' => $this->color
            ));
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
        $this->data = array($this->for, $this->show, $this->width, $this->height, $this->scale, $this->color, $this->fixcontent);
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->for = $this->data[0];
        $this->show = $this->data[1];
        if(isset($this->data[2]))$this->width = $this->data[2];
        if(isset($this->data[3]))$this->height = $this->data[3];
        if(isset($this->data[4]))$this->scale = $this->data[4];
        if(isset($this->data[5]))$this->color = $this->data[5];
        if(isset($this->data[6]))$this->fixcontent = $this->data[6];
        $this->data = array();
    }
}
?>