<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2011-03-09
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
class TCmdPiwik
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
    private $base = '', $id = 0;
    public $data = array();
    
    public function __construct(DOMNode $node)
    {
        $atts = $node->attributes;
        $baseNode = $atts->getNamedItem('base');
        $idNode = $atts->getNamedItem('site');
        if(!$baseNode || !$idNode)
        {
            return;
        }
        $this->base = $baseNode->nodeValue;
        $this->id = $idNode->nodeValue;
    }
    
    public function setUp(array $environment)
    {
    }
    
    public function run(array $environment)
    {
		if(empty($this->base) || empty ($this->id)){
			return '';
		}
		return '<script type="text/javascript">'.
			sprintf('var piwikBase = "%s",'.
				'piwikId = %d;', $this->base, $this->id).
		
			'window.setTimeout(function(){'.
				'var script = document.createElement("script"),'.
					'piwikRunner = function(){'.
						'var piwikTracker = Piwik.getTracker(piwikBase + "piwik.php", piwikId);'.
						'piwikTracker.trackPageView();'.
						'piwikTracker.enableLinkTracking();'.
					'};'.
				'script.type = "text/javascript";'.
				'script.src = piwikBase+"piwik.js";'.
				'script.onload = piwikRunner;'.
				'script.onreadystatechange = function () { var x =  (this.readyState == "complete") ? piwikRunner() : null;  };'.
				'document.body.appendChild(script);'.
			'}, 400);'.

		'</script>'.
		sprintf('<noscript><img src="%spiwik.php?idsite=%d" style="display:none;" alt="" /></noscript>', $this->base, $this->id);
	}
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        $this->data = array($this->base, $this->id);
        return array('data');
    }
    
    public function __wakeup()
    {
        $this->base = $this->data[0];
        $this->id = $this->data[1];
        $this->data = array();
    }
}
?>