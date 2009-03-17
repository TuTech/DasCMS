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
    extends 
        BSystem 
    implements 
        IShareable
{	
    private $name, $description, $guid, $version, $icon, $interface, $class;
    /**
     * @var WApplicationTaskBar
     */
    private $controller = null;
    private $taskbar;
    private $openDialog = false;
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
            $a = self::alloc()->init();
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
    		    LConfiguration::get('sitename')
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
            $ofd = WOpenDialog::alloc()->init();
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
        if(RURL::has('editor'))
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
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->load($appXML);
        $dom->validate();
        $xp = new DOMXPath($dom);
        $atts = array(
            'guid'        => '/bambus/appController/@guid',
            'class'       => '/bambus/appController',
        	'name'        => '/bambus/name',
            'description' => '/bambus/description',
            'icon'        => '/bambus/icon',
            'version'     => '/bambus/version',
            'controller'  => '/bambus/application/controller',
            'interface'   => '/bambus/application/interface/@src'
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
        $panel = WSidePanel::alloc()->init();
        $panel->setMode($mode);
        $processInputs = $xp->query('/bambus/application/sidebar/processInputs/@mode');
        if($processInputs  && $processInputs->length == 1);
        {
            $panel->setProcessMode(strval($processInputs->item(0)->nodeValue));
        }
    }
    
    
	//begin IShareable
	const CLASS_NAME = 'SApplication';
	
	public static $sharedInstance = NULL;
	
	/**
	 * @return SApplication
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
     * @return SApplication
     */
    function init()
    {
    	return $this;
    }
	//end IShareable
}

?>