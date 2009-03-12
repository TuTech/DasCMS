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
    
    private $hotkeys = array();
    
    public function __construct()
    {
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
        if($atts['hotkey'] != '')
        {
            $hotkeyID = 'id="App-Hotkey-CTRL-'.$atts['hotkey'].'"';
            $this->hotkeys[$atts['hotkey']] = $action;
        }
        return sprintf(
            "\t\t<a class=\"CommandBarPanelItem\" %stitle=\"%s\" href=\"javascript:%s\">%s</a>\n"
            ,$hotkeyID
            ,SLocalization::get($atts['caption'])
            ,$action
            ,new WIcon($atts['icon'], SLocalization::get($atts['caption']))
        );
    }
    
    private function buildTaskSwitch(DOMNode $node)
    {
        
    }
    
    
    
//    function generateTaskBar()
//    {//TASK- not Tab-Bar
//        $applicationNode = $this->getXMLNodeByPathAndAttribute('bambus/application/interface', 'name', $this->tab);
//        $CommandBar = '';
//        if(!empty($applicationNode[0]))
//        {
//            $tasks = $this->getSCTagValues('task', $applicationNode[0]);
//            $first = true;
//            $closed = true;
//            $panelName = '';
//            $CommandBar = "<div id=\"CommandBar\">\n";
//            $panelID = '';
//            $hotkeys = array();
//            $firstPaelElement = true;
//            $panelIDCntr = 0;
//            foreach($tasks as $task)
//            {
//                
//                $hotkeyID = '';
//                switch($task['type'])
//                {
//                    case('spacer'):
//                        $firstPaelElement = true;
//                        $panelName = isset($task['name']) ?  SLocalization::get($task['name']) : '';
//                        $panelID = isset($task['name']) ?  $task['name'] : ++$panelIDCntr;
//                        if(!$closed)
//                        {
//                            $CommandBar .= "</div>\n";
//                            $closed = true;
//                        }
//                        $CommandBar .= "<span class=\"CommandBarSpacer\"></span>\n";
//                        break;
//                    case('button'):
//                        $doJS = (empty($task['mode']) || strtolower($task['mode']) == 'javascript');
//
//                        if($doJS)
//                        {
//                            $action = $task['action'];
//                            if(!empty($task['confirm']))
//                            {
//                                $action = sprintf("if(confirm('%s')){%s}", SLocalization::get($task['confirm']), $action);
//                            }
//                        }
//                        else
//                        {
//                            $action = '';
//                            if(!empty($task['confirm']))
//                            {
//                                $action = sprintf("if(confirm('%s'))", utf8_encode(html_entity_decode(SLocalization::get($task['confirm']))));
//                            }
//                            $prompt = '';
//                            if(!empty($task['prompt']))
//                            {
//                                $prompt = "+'&amp;prompt='+prompt('".addslashes($task['prompt'])."')";
//                            }
//                            $action .= sprintf("{top.location = '%s'%s;}", addslashes(SLink::link(array('_action' => $task['action']))), $prompt);
//                        }
//                        if(!empty($task['hotkey']))
//                        {
//                            $hotkeyID = 'id="App-Hotkey-CTRL-'.$task['hotkey'].'"';
//                            $hotkeys[$task['hotkey']] = $action;
//                        }
//                        if($closed)
//                        {
//                            $CommandBar .= sprintf(
//                                "<div id=\"CommandBarPanel_%s\" class=\"CommandBarPanel\" title=\"%s\">\n"
//                                ,$panelID
//                                ,htmlentities($panelName, ENT_QUOTES, 'UTF-8')
//                            );
//                            $closed = false;
//                        }                       
//                        $CommandBar .= sprintf(
//                            "<a class=\"CommandBarPanelItem%s\" %stitle=\"%s\" href=\"javascript:%s\">%s</a>"
//                            ,$firstPaelElement ? ' CommandPanelItemFirst' : ''
//                            ,$hotkeyID
//                            ,SLocalization::get($task['caption'])
//                            ,$action
//                            ,new WIcon($task['icon'], SLocalization::get($task['caption']))
//                        );
//                        $firstPaelElement = false;
//                        break;
//                }
//                $first = false;
//            }
//            if(!$closed)
//            {
//                $CommandBar .= '</div>';
//            }
//            $hk = '';
//            foreach ($hotkeys as $key => $action)
//            {
//                $hk .= sprintf('org.bambuscms.app.hotkeys.register("CTRL-%s",function(){%s});%s',$key,$action,"\n");
//            }
//            $CommandBar .= new WScript($hk); 
//            $CommandBar .= '<br class="CommandBarTerminator" /></div>';
//        }
//        return $CommandBar;
//    }
}
?>