<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class View_UIElement_ApplicationTaskBar
{
    /**
     * @var DOMDocument
     */
    private $dom = null;
    private $searchable = false;
    private $hotkeys = array();
    
    public function __construct()
    {
    }
    
    public function setSearchable($yn)
    {
        $this->searchable = $yn == true;
    }
    
    public function setSource(DOMDocument $appDom)
    {
        $this->dom = $appDom;
    }
    
    public function render()
    {
        echo strval($this);
    }
    
    public function __toString()
    {
        $html = "<div id=\"CommandBar\">\n";
        if(is_object($this->dom))
        {
            $xp = new DOMXPath($this->dom);
            $panels = $xp->query('/bambus/application/interface/panel');
            foreach ($panels as $panel)
            {
                $name = $xp->query('@name', $panel)->item(0)->nodeValue;
                $html .= sprintf(
                	"\t<div id=\"CommandBarPanel_%s\" class=\"CommandBarPanel\">\n"
                	,$name
                );
                $tasks = $xp->query('task', $panel);
                foreach($tasks as $task)
                {
                    $type = $xp->query('@type', $task)->item(0)->nodeValue;
                    $html .= $this->{'buildTask'.ucfirst(strtolower($type))}($task, $xp);
                }
                $html .= "\t</div>\n";
            }
        }
        if($this->searchable)
        {
            $html .= "\t<div id=\"CommandBar_Search\">".
            	"\n\t\t<input type=\"text\" id=\"CommandBar_Search_Input\" ".
            	"\n\t</div>\n";
        }
        $html .= "</div>\n";
        return $html;
    }
    
    private function getAtts(DOMNode $node, DOMXPath $xp, $atts)
    {
        foreach ($atts as $k => $v)
        {
            $data = $xp->query('@'.$k, $node);
            if($data && $data->length)
            {
                $atts[$k] = $data->item(0)->nodeValue;
            }
        }   
        return $atts;    
    }
    
    private function buildTaskButton(DOMNode $node, DOMXPath $xp)
    {
        $atts = array('caption' => '' , 'icon' => '' , 'name' => '' , 'hotkey' => '', 'confirm' => '' , 'action' => '' , 'mode' => '');
        $atts = $this->getAtts($node, $xp, $atts);
		
		if(empty($atts['icon'])){
			$atts['icon'] = 'action-document-'.$atts['action'];
		}
		
		$caption = SLocalization::get($atts['caption']);
		return sprintf(
				"<span class=\"CommandBarPanelItem\" title=\"%s\" data-action=\"%s\" data-hotkey=\"%s\">%s</span>",
				$caption,
				String::htmlEncode($atts['action']),
				String::htmlEncode($atts['hotkey']),
				new View_UIElement_Icon($atts['icon'], $caption)
		);
    }
    
    private function buildTaskSwitch(DOMNode $node)
    {
        
    }
}
?>