<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
class TCmdContent
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $alias;
    private $property = 'Content';
    private static $contents = array();
    public $data = array();
    
    public function __construct(DOMNode $node)
    {
        $atts = $node->attributes;
        $getNode = $atts->getNamedItem('alias');
        $propNode = $atts->getNamedItem('property');
        if(!$getNode)
        {
            return;
        }
        $this->alias = $getNode->nodeValue;
        if(!$propNode)
        {
            return;
        }
        $this->property = $propNode->nodeValue;
    }
    
    public function setUp(array $environment)
    {
        if(!array_key_exists($this->alias, self::$contents))
        {
            try
            {
                self::$contents[$this->alias] = BContent::Access($this->alias, $this);
            }
            catch (Exception $e)
            {
                unset(self::$contents[$this->alias]);
            }
        }
    }
    
    public function run(array $environment)
    {
        if(array_key_exists($this->alias, self::$contents)
            && self::$contents[$this->alias] instanceof BContent
            && isset(self::$contents[$this->alias]->{$this->property}))
        {
            return self::$contents[$this->alias]->{$this->property};
        }
        return '';
    }
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        $this->data = array($this->alias, $this->property);
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->alias = $this->data[0];
        $this->property = $this->data[1];
        $this->data = array();
    }
}
?>