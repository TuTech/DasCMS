<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 29.07.2006
 * @license GNU General Public License 3
 */
class LApplication extends BLegacy implements IShareable
{
    //IShareable
    const CLASS_NAME = 'LApplication';
    public static $sharedInstance = NULL;
    private static $initializedInstance = false;
    
    private static $appdata = array(
        'icon' => '',
        'dir' => '',
        'title' => '',
    	'name' => ''
    );
    
    /**
     * @return LApplication
     */
    public static function alloc()
    {
        $class = self::CLASS_NAME;
        if(self::$sharedInstance == NULL && $class != NULL)
        {
            self::$sharedInstance = new $class();
        }
        return self::$sharedInstance;
    }
    
    /**
     * @return LApplication
     */
    function init()
    {
        if(!self::$initializedInstance)
        {
            self::$initializedInstance = true;
        }
        return $this;
    }
    //end IShareable
    
    
    
    public $name, $bad, $xml, $applicationDirectory, $interfaceXML;
    public $initialized = false;
    public $tab = '';  
    
            
//////////////////////////////////////////////////////////////
//// Class functions
//////////////////////////////////////////////////////////////
    
    function autorun()
    {
        if($this->initApp())
        {//application valid
            define('BAMBUS_XML_NODE_VALUE', 0);
            define('BAMBUS_XML_NODE_ATTRIBUTES', 1);
                        
            //set the active tab
            $tabs = $this->getXMLPathValueAndAttributes('bambus/tabs/tab');
            if(!isset($tabs[0]))
            {//no tabs in xml
                //create default "edit"-tab
                $tabs[0] = array('edit', array('icon' => 'edit'));
            }
            $availableTabs = array();
            foreach($tabs as $tab)
            {
                $availableTabs[] = $tab[0];
            }
            if(!RURL::has('tab') || !in_array(RURL::get('tab'), $availableTabs))
            {
                $this->tab = $tabs[0][0];
            }
            else
            {
                $this->tab = RURL::get('tab');
            }
        }
    }

    function controller()
    {
        //load controll php
        $valueAndAttributes = $this->getXMLPathValueAndAttributes('bambus/application/controller');
        $path = self::getDirectory();
        if(!empty($valueAndAttributes[0][0]))
        {
            return($path.$valueAndAttributes[0][0]);
        }
        return false;
    }
        
    function run()
    {
        $applicationNode = $this->getXMLNodeByPathAndAttribute('bambus/application/interface', 'name',$this->tab);
        $path = self::getDirectory();
        if(!empty($applicationNode[1]['src']))
        {
            //generate gui by php
            return $path.$applicationNode[1]['src'];
        }
        return false;
    }

    function generateTaskBar()
    {//TASK- not Tab-Bar
        $applicationNode = $this->getXMLNodeByPathAndAttribute('bambus/application/interface', 'name', $this->tab);
        $CommandBar = '';
        if(!empty($applicationNode[0]))
        {
            $tasks = $this->getSCTagValues('task', $applicationNode[0]);
            $first = true;
            $closed = true;
            $panelName = '';
            $CommandBar = "<div id=\"CommandBar\">\n";
            $panelID = '';
            $hotkeys = array();
            $firstPaelElement = true;
            $panelIDCntr = 0;
            foreach($tasks as $task)
            {
                
                $hotkeyID = '';
                switch($task['type'])
                {
                    case('spacer'):
                        $firstPaelElement = true;
                        $panelName = isset($task['name']) ?  SLocalization::get($task['name']) : '';
                        $panelID = isset($task['name']) ?  $task['name'] : ++$panelIDCntr;
                        if(!$closed)
                        {
                            $CommandBar .= "</div>\n";
                            $closed = true;
                        }
                        $CommandBar .= "<span class=\"CommandBarSpacer\"></span>\n";
                        break;
                    case('button'):
                        $doJS = (empty($task['mode']) || strtolower($task['mode']) == 'javascript');

                        if($doJS)
                        {
                            $action = $task['action'];
                            if(!empty($task['confirm']))
                            {
                                $action = sprintf("if(confirm('%s')){%s}", SLocalization::get($task['confirm']), $action);
                            }
                        }
                        else
                        {
                            $action = '';
                            if(!empty($task['confirm']))
                            {
                                $action = sprintf("if(confirm('%s'))", utf8_encode(html_entity_decode(SLocalization::get($task['confirm']))));
                            }
                            $prompt = '';
                            if(!empty($task['prompt']))
                            {
                                $prompt = "+'&amp;prompt='+prompt('".addslashes($task['prompt'])."')";
                            }
                            $action .= sprintf("{top.location = '%s'%s;}", addslashes(SLink::link(array('_action' => $task['action']))), $prompt);
                        }
                        if(!empty($task['hotkey']))
                        {
                            $hotkeyID = 'id="App-Hotkey-CTRL-'.$task['hotkey'].'"';
                            $hotkeys[$task['hotkey']] = $action;
                        }
                        if($closed)
                        {
                            $CommandBar .= sprintf(
                                "<div id=\"CommandBarPanel_%s\" class=\"CommandBarPanel\" title=\"%s\">\n"
                                ,$panelID
                                ,htmlentities($panelName, ENT_QUOTES, 'UTF-8')
                            );
                            $closed = false;
                        }                       
                        $CommandBar .= sprintf(
                            "<a class=\"CommandBarPanelItem%s\" %stitle=\"%s\" href=\"javascript:%s\">%s</a>"
                            ,$firstPaelElement ? ' CommandPanelItemFirst' : ''
                            ,$hotkeyID
                            ,SLocalization::get($task['caption'])
                            ,$action
                            ,new WIcon($task['icon'], SLocalization::get($task['caption']))
                        );
                        $firstPaelElement = false;
                        break;
                }
                $first = false;
            }
            if(!$closed)
            {
                $CommandBar .= '</div>';
            }
            $hk = '';
            foreach ($hotkeys as $key => $action)
            {
                $hk .= sprintf('org.bambuscms.app.hotkeys.register("CTRL-%s",function(){%s});%s',$key,$action,"\n");
            }
            $CommandBar .= new WScript($hk); 
            $CommandBar .= '<br class="CommandBarTerminator" /></div>';
        }
        return $CommandBar;
    }
        
    function initApp()
    {
        $path = self::getDirectory();
        $xmlfile = $path.'Application.xml';
        if(file_exists($xmlfile))
        {
            $this->xml = DFileSystem::Load($xmlfile);
            $this->initialized = true;
            return true;
        }
        else
        {
            return false;
        }
    }
    
    function getSCTagValues($tagName, $xml = false)
    {
        if($xml == false)
        {
            $xml = $this->xml;
        }
        $preg = array();
        preg_match_all("/<".$tagName."([^>]*)\/>/muiU", $xml, $preg);
        $nodes = array();
        for($i = 0; $i < count($preg[0]); $i++)
        {
            $attributes =  (isset($preg[1][$i])) ? $preg[1][$i] : '';
            if(empty($attributes))
            {
                $attributelist = array();
            }
            else
            {       
                $kpreg = array();
                $attributelist = array();
                preg_match_all("/([\\w]+)[\\s]*=[\\s]*\"([^\"]+)\"/muiU", $attributes, $kpreg);
                for($k = 0; $k < count($kpreg[0]);$k++)
                {
                    $attributelist[$kpreg[1][$k]] = $kpreg[2][$k];
                }
            }
            $nodes[$i] = $attributelist;
        }
        return $nodes;
    }
    
    function getXMLNodeByPathAndAttribute($path, $attribute, $attributeQuery, $xml = false)
    {
        if($xml == false)
        {
            $xml = $this->xml;
        }
        $nodes = $this->getXMLPathValueAndAttributes($path, $xml);
        $i = 0;
        while(isset($nodes[$i]))
        {
            if(isset($nodes[$i][1][$attribute]) && $nodes[$i][1][$attribute] == $attributeQuery)
            {
        //FOUND! >>STOP HERE<<
                return $nodes[$i];
            }
            $i++;
        }
        return false;
    }
    
    function arrayFromAttributeString($attributes)
    {
        $attributelist = array();
        $kpreg = array();
        preg_match_all("/([\\w]+)[\\s]*=[\\s]*\"([^\"]+)\"/muiU", $attributes, $kpreg);
        for($k = 0; $k < count($kpreg[0]);$k++)
        {
            $attributelist[$kpreg[1][$k]] = $kpreg[2][$k];
        }
        return $attributelist;
    }
    
    function getXMLPathValueAndAttributes($path, $xml = false)
    {
        if($xml == false)
        {
            $xml = $this->xml;
        }
        $stages = explode('/', $path);
        while(count($stages) > 1)
        {
            $preg = array();
            preg_match("/<".$stages[0]."[^>]*>([^\\0]*)<\/".$stages[0].">/mui", $xml, $preg);
            $xml = (isset($preg[1])) ? $preg[1] : ''; 
            unset($stages[0]);
            $stages = array_values($stages);
        }
        $preg = array();
        preg_match_all("/<".$stages[0]."([^>]*)>([^\\0]*)<\/".$stages[0].">/muiU", $xml, $preg);
        $nodes = array();
        for($i = 0; $i < count($preg[0]); $i++)
        {
            
            $nodevalue = (isset($preg[2][$i])) ? $preg[2][$i] : '';
            $attributes =  (isset($preg[1][$i])) ? $preg[1][$i] : '';
            if(empty($attributes))
            {
                $attributelist = array();
            }
            else
            {       
                $attributelist = $this->arrayFromAttributeString($attributes);
            }
            $nodes[$i] = array($nodevalue, $attributelist);
        }
        return $nodes;
    }
    
    public static function getBambusApplicationDescription($xmlfile)
    {
        $requests = array('name' => '', 'description' => '', 'icon' => '',  
            'priority' => '', 'version' => '', 'purpose' => 'other');
        $xml = DFileSystem::Load($xmlfile);
        foreach($requests as $node => $value)
        {
            preg_match("/<".$node.">(.*)<\/".$node.">/", $xml, $preg);
            $requests[$node] = (isset($preg[1])) ? $preg[1] : $requests[$node];
        }

        preg_match_all("/<tab[\\s]+icon=\"([a-zA-Z0-9-_]+)\">(.*)<\\/tab>/", $xml, $matches);
        for($i = 0; $i < count($matches[0]); $i++)
        {
            $requests['*'.$matches[2][$i]] = $matches[1][$i];
        }
        return $requests;
    }
    
    ////////////////////////////////////
    //define available applications//
    ////////////////////////////////////
    public static function getAvailableApplications()
    {
        $i = 0;
        $available = array();
        chdir(SPath::SYSTEM_APPLICATIONS);
        $Dir = opendir ('./');
        while ($item = readdir ($Dir)) 
        {
            if(is_dir($item) && file_exists($item.'/Application.xml') && substr($item,0,1) != '.' && strtolower(DFileSystem::suffix($item)) == 'bap')
            {
                $i++;
                
                $data = self::getBambusApplicationDescription($item.'/Application.xml');
                $tabs = array();
                foreach ($data as $tab => $icon) 
                {
                    if(substr($tab,0,1) == '*')
                    {
                        $tabs[substr($tab,1)] = $icon;
                    }
                }
                //'name' => '', 'description' => '', 
                //'icon' => '', 'priority' => '', 
                //'version' => '', 'purpose' => 'other'
                $available[$item] = array(
                    'purpose' => 'other'
                    ,'item' => $item
                    ,'name' => $data['name']
                    ,'desc' => $data['description']
                    ,'icon' => $data['icon']
                    ,'type' => 'application',
                    'tabs' => $tabs);
            }        
        }
        closedir($Dir);
        chdir(BAMBUS_CMS_ROOTDIR);
        
        if(!PAuthorisation::isInGroup('Administrator'))
        {
            $keys = array_keys($available);
            foreach($keys as $id)
            {
                $appName = substr($id,0,(strlen(DFileSystem::suffix($id))+1)*-1);
                $SUsersAndGroups = SUsersAndGroups::alloc()->init();
                if(!$SUsersAndGroups->hasPermission(PAuthentication::getUserID(), $appName))
                {
                    unset($available[$id]);
                }
            
            }
        }
        return $available;
    }
    
    public function selectApplicationFromPool($pool = array())
    {
        $barCompatibleTabs = array();
        if(RURL::has('editor') 
            && in_array(RURL::get('editor'), array_keys($pool))
            && file_exists(SPath::SYSTEM_APPLICATIONS.RURL::get('editor').'/Application.xml'))
        {
            
            self::$appdata = array(
                'icon' => WIcon::pathFor($pool[RURL::get('editor')]['icon'],'app'),
                'dir' => SPath::SYSTEM_APPLICATIONS.RURL::get('editor').'/',
                'title' => SLocalization::get($pool[RURL::get('editor')]['name']),
            	'name' => RURL::get('editor')
            );

            $tabs = $this->getXMLPathValueAndAttributes('bambus/tabs/tab');
            if(!isset($tabs[0]))
            {   //no tabs in xml? - create default "edit"-tab
                $tabs[0] = array('edit', array('icon' => 'edit'));
            }
            $activeTab = $tabs[0];
        }
        return $barCompatibleTabs;
    }
    public static function setAppData(array $data)
    {
        foreach (self::$appdata as $k => $v) 
        {
            if(isset($data[$k]))
            {
                self::$appdata[$k] = $data[$k];
            }
        }
    }
    
    public static function getName()
    {
        return self::$appdata['name'];
    }
    
    public static function getTitle()
    {
        return self::$appdata['title'];
    }
    
    public static function getIcon()
    {
        return self::$appdata['icon'];
    }
    
    public static function getDirectory()
    {
        return self::$appdata['dir'];
    }
        
}
?>
