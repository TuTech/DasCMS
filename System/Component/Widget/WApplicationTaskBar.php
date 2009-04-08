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
class WApplicationTaskBar
    extends 
        BSystem 
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
        $html .= "\t<script type=\"text/javascript\">\n";
        foreach ($this->hotkeys as $key => $action)
        {
            $html .= sprintf("\t\t".'org.bambuscms.app.hotkeys.register("CTRL-%s",function(){%s});%s',$key,$action,"\n");
        }
        $html .= "\t</script>\n";
        if($this->searchable)
        {
            $html .= "\t<div id=\"CommandBar_Search\">".
            	"\n\t\t<input type=\"text\" id=\"CommandBar_Search_Input\" ".
            	"onkeyup=\"org.bambuscms.app.search(this.value);\" ".
            	"onmouseup=\"org.bambuscms.app.search(this.value);\" />".
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
        $action = (strtolower($atts['mode']) == 'html') 
            ? sprintf("{top.location = '%s';}", addslashes(SLink::link(array('_action' => $atts['action']))))
            : $atts['action'];
        if($atts['confirm'] != '')
        {
            $action = sprintf(
            	"if(confirm(_('%s'))){%s}"
            	,htmlentities($atts['confirm'], ENT_QUOTES, 'utf-8')
            	,$action
            );
        }
        $hotkeyID = '';
        $hkttl = '';
        if($atts['hotkey'] != '')
        {
            $hotkeyID = 'id="App-Hotkey-CTRL-'.$atts['hotkey'].'"';
            $this->hotkeys[$atts['hotkey']] = $action;
            $hkttl = ' ('.SLocalization::get('CTRL').'-'.$atts['hotkey'].')';
        }
        return sprintf(
            "\t\t<a class=\"CommandBarPanelItem\" %stitle=\"%s%s\" href=\"javascript:nil();\" onmousedown=\"%s;return false;\">%s</a>\n"
            ,$hotkeyID
            ,SLocalization::get($atts['caption'])
            ,$hkttl
            ,$action
            ,new WIcon($atts['icon'], SLocalization::get($atts['caption']).$hkttl)
        );
    }
    
    private function buildTaskSwitch(DOMNode $node)
    {
        
    }
}
?>