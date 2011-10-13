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
	private $content;
    
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
        	$this->content = $editor->getOpenDialogTarget();
        }
        if($this->content == null)
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
		return sprintf("<input type=\"hidden\" id=\"document-form-content\" name=\"_edit\" value=\"%s\">", String::htmlEncode($this->content))
			.sprintf("<input type=\"hidden\" id=\"document-form-app\" name=\"_app\" value=\"%s\">", String::htmlEncode(RURL::get('editor')))
			.sprintf("<input type=\"hidden\" id=\"document-form-data-source\" name=\"_src\" value=\"%s\">", String::htmlEncode($this->editor->getClassGUID()));
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