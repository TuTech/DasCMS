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
class WOpenDialog extends BWidget implements IShareable
{
	//IShareable
	const CLASS_NAME = 'WOpenDialog';
	/**
	 * @var WOpenDialog
	 */
	public static $sharedInstance = NULL;
	/**
	 * @return WOpenDialog
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
	
    protected static $CurrentWidgetID = 0;
    /**
     * @var BAppController
     */
    private $editor;
    private $autoload = false;
    private $source = null;
    
    public function autoload($yn = true)
    {
        $this->autoload = ($yn == true);
    }
    
    public function __construct($editor = null, $BContent = null)
    {
        if ($editor instanceof BAppController) 
        {
        	$this->setTarget($editor, $BContent);
        }
    }
    
    public function setTarget(BAppController $editor, $BContent = null)
    {
        $this->editor = $editor;
        if ($editor instanceof ISupportsOpenDialog) 
        {
        	$BContent = $editor->getOpenDialogTarget();
        }
        if($BContent == null)
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
         return strval(new WScript($script));
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