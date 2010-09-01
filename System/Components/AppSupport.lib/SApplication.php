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
class SApplication 
	extends BObject
    implements 
        IShareable
{	
    private $name, $description, $guid, $version, $icon, $interface, $class, $purpose;
    /**
     * @var WApplicationTaskBar
     */
    private $controller = null;
    private $taskbar;
    private $openDialog = false;
    private $searchable = 'no';
    private $openDialogAutoShow;
    private $appPath, $hasApp = false;
    private static $appController = null;
    /**
     * @return BAppController
     */
    public static function appController()
    {
        if(self::$appController == null)
        {
            $a = self::getInstance();
            self::$appController = BAppController::getControllerForID($a->getGUID());
        }
        return self::$appController;
    }
    
    public static function getControllerContent()
    {
        $ctrl = self::appController();
        $data = $ctrl->getSideBarTarget();
        $out = null;
        if(count($data))
        {
            $out = $data[0];
        }
        return $out;
    }
    
    public function initApplication()
    {
        if($this->hasApp)
        {
            $appFiles = array(
            	'style.css' => 'screen',
            	'print.css'=>'print', 
            	'script.js' => 'script');
    		foreach($appFiles as $file => $type)
    		{
    			if(!file_exists($this->appPath.$file))
    				continue;
    			switch($type)
    			{
    				case 'script':
    					WHeader::useScript($this->appPath.$file);
    					break;
    				default: //css
    					WHeader::useStylesheet($this->appPath.$file, $type);
    			}
    		}
    		WHeader::setTitle(
    			'Bambus CMS: '.
    		    SLocalization::get($this->name).' - '.
    		    Core::settings()->get('sitename')
		    );
        }
        else
        {
            WHeader::setTitle('Bambus CMS');
        }
    }
    
    public function hasApplication()
    {
        return $this->hasApp;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function getGUID()
    {
        return $this->guid;
    }
    
    public function getVersion()
    {
        return $this->version;
    }    
    
    public function getPurpose()
    {
        return $this->version;
    }
    
    public function getIcon()
    {
        return $this->icon;
    }
    
    public function getClass()
    {
        return $this->class;
    }
    
    public function getInterface()
    {
        return $this->appPath.$this->interface;
    }    
    
    public function getController()
    {
        return ($this->controller == null) ? null : $this->appPath.$this->controller;
    }
    
    public function getTaskBar()
    {
        return $this->taskbar;
    }
    
    public function getOpenDialog()
    {
        $ofd = '';
        if($this->openDialog)
        {
            $ofd = WOpenDialog::getInstance();
            $ofd->setTarget(self::appController());
            if(!$this->openDialogAutoShow)
            {
                $ofd->autoload(false);
            }
        }
        return $ofd;
    }
    
    protected function __construct()
    {
        $this->taskbar = new WApplicationTaskBar();
        if(RURL::hasValue('editor'))
        {
            $app = basename(RURL::get('editor'));
            $appXML = SPath::SYSTEM_APPLICATIONS.$app.'/Application.xml';
            $this->appPath = SPath::SYSTEM_APPLICATIONS.$app.'/';
            if(!file_exists($appXML))
            {
                throw new XFileNotFoundException('Application not found');
            }
            $this->loadApplicationData($appXML);
            if(!PAuthorisation::has($this->guid))
            {
                throw new XPermissionDeniedException($app);
            }
            $this->hasApp = true;
        }
        //load js and css into wtpl
    }
    
    private function loadApplicationData($appXML)
    {
        $dom = new DOMDocument('1.0', CHARSET);
        $dom->load($appXML);
        $dom->validate();
        $xp = new DOMXPath($dom);
        $atts = array(
            'guid'        => '/bambus/appController/@guid',
            'class'       => '/bambus/appController',
        	'name'        => '/bambus/name',
            'description' => '/bambus/description',
            'purpose'     => '/bambus/purpose',
        	'icon'        => '/bambus/icon',
            'version'     => '/bambus/version',
            'controller'  => '/bambus/application/controller',
            'interface'   => '/bambus/application/interface/@src',
            'searchable'  => '/bambus/application/interface/@searchable'
        );
        foreach ($atts as $var => $xpath)
        {
            $data = $xp->query($xpath);
            if($data && $data->length == 1)
            {
                $this->{$var} = $data->item(0)->nodeValue;
            }
        }
        $this->setupSidebar($xp);
        $this->setupOpenDialog($xp);
        $this->taskbar->setSource($dom);
        $this->taskbar->setSearchable($this->searchable == 'yes');
    }
    
    private function setupOpenDialog(DOMXPath $xp)
    {
        $supported = $xp->query('/bambus/application/openDialog/@autoShow');
        if($supported && $supported->length == 1)
        {
            $this->openDialogAutoShow = strtolower($supported->item(0)->nodeValue) == 'yes';
            $this->openDialog = true;
        }
    }
    
    private function setupSidebar(DOMXPath $xp)
    {
        $supported = $xp->query('/bambus/application/sidebar/supported/@mode');
        $mode = WSidePanel::NONE;
        foreach ($supported as $modeNode)
        {
            $const = constant('WSidePanel::'.$modeNode->nodeValue);
            if($const)
            {
                $mode = $mode | $const;
            }
        }
        $panel = WSidePanel::getInstance();
        $panel->setMode($mode);
        $processInputs = $xp->query('/bambus/application/sidebar/processInputs/@mode');
        if($processInputs  && $processInputs->length == 1);
        {
            $panel->setProcessMode(strval($processInputs->item(0)->nodeValue));
        }
    }
    
	public static function listApplications()
	{
		$available = array();
		$appPath = SPath::SYSTEM_APPLICATIONS;
		$dirhdl = opendir($appPath);
		while($item = readdir($dirhdl))
		{
			if(is_dir($appPath.$item) 
				&& substr($item,0,1) != '.' 
				&& strtolower(substr($item,-4)) == '.bap' 
				&& file_exists($appPath.$item.'/Application.xml')
			)
			{
			    $d = new DOMDocument();
			    $d->load(realpath($appPath.$item.'/Application.xml'));
			    $xp = new DOMXPath($d);
			    $perm = $xp->query('/bambus/appController/@guid')->item(0)->nodeValue;
			    if(!PAuthorisation::has($perm))
			    {
			        unset($xp);
			        unset($d);
			        continue;
			    }
			    $atts = array(
					 'name'    => '/bambus/name'
					,'desc'    => '/bambus/description'
					,'icon'    => '/bambus/icon'
					,'purpose' => '/bambus/purpose'
					,'tabs'    => '/bambus/tabs/tab'
			    );
			    foreach ($atts as $a => $q)
			    {
			        $r = $xp->query($q);
			        if($a != 'tabs')
			        {
			            $atts[$a] = $r->item(0)->nodeValue;
			        }
			        else
			        {
			            $atts[$a] = array();
			            for($i = 0; $i < $r->length; $i++)
			            {
			                $atts[$a][$r->item($i)->nodeValue] = $xp->query('@icon', $r->item($i))->item(0)->nodeValue;
			            }
			            
			        }
			    }
			    $atts['active'] = false;
			    unset($xp);
		        unset($d);
		        $available[$item] = $atts;
				$app = substr($item,0,((strlen(DFileSystem::suffix($item))+1) * -1));
			}
		}
		closedir($dirhdl);
		
		//selecting app
		$selectedApp = RURL::get('editor');
		if(!empty($selectedApp) && isset($available[$selectedApp]))
		{
			//select tab
			$selectedTab = RURL::get('tab');
			//correct if necessary
			if(!array_key_exists($selectedTab, $available[$selectedApp]['tabs']))
			{
				//right app, wrong tab
				$tabs = array_keys($available[$selectedApp]['tabs']);
				if(count($tabs) > 0)
				{
					$selectedTab = $tabs[0];
				}
			}
			//prevent failure if no tabs exists
			if(array_key_exists($selectedTab, $available[$selectedApp]['tabs']))
			{
				$available[$selectedApp]['active'] = $selectedTab;
			}
		}
		return $available;
	}
    
	//begin IShareable
	const CLASS_NAME = 'SApplication';
	
	public static $sharedInstance = NULL;
	
	/**
	 * @return SApplication
	 */
	public static function getInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end IShareable
}

?>