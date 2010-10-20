<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-07
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_OpenDialog extends _View_UIElement implements Interface_Singleton
{
	//Interface_Singleton
	const CLASS_NAME = 'View_UIElement_OpenDialog';
	/**
	 * @var View_UIElement_OpenDialog
	 */
	public static $sharedInstance = NULL;
	/**
	 * @return View_UIElement_OpenDialog
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
	//end Interface_Singleton
	
    protected static $CurrentWidgetID = 0;
    /**
     * @var _Controller_Application
     */
    private $editor;
    private $autoload = false;
    private $source = null;
    
    public function autoload($yn = true)
    {
        $this->autoload = ($yn == true);
    }
    
    public function __construct($editor = null, $_Content = null)
    {
        if ($editor instanceof _Controller_Application) 
        {
        	$this->setTarget($editor, $_Content);
        }
    }
    
    public function setTarget(_Controller_Application $editor, $_Content = null)
    {
        $this->editor = $editor;
        if ($editor instanceof ISupportsOpenDialog) 
        {
        	$_Content = $editor->getOpenDialogTarget();
        }
        if($_Content == null)
        {
            $this->autoload(true);
        }
    }
    
    /**
     * return rendered html
     *
     */
    public function __toString()
    {
        $script =
            'org.bambuscms.wopenfiledialog.prepareLinks("'.
                SLink::link(array('edit' => '')).
             '","");';
         if($this->autoload)
         {
            $script .= 
                'org.bambuscms.wopenfiledialog.closable = false;'.
                'org.bambuscms.wopenfiledialog.show();';
         }   
         return strval(new View_UIElement_Script($script));
    }
    
    /**
     * process inputs etc
     *
     */
    public function run(){} 
    
    /**
     * echo html 
     */
    public function render()
    {
        echo $this->__toString();
    }
    /**
     * return ID of primary editable element or null 
     *
     * @return string|null
     */
    public function getPrimaryInputID()
    {
        return null;
    }
}
?>