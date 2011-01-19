<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-08-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_Script extends _View_UIElement 
{
    const CLASS_NAME = "View_UIElement_Script";
    private $id;
    private $language = 'javascript';
    private $script;
    public function __construct($target)
    {       
        $this->id = ++parent::$CurrentWidgetID;
        $this->script = $target;
    }

    public function setLanguage($language)
    {
    	$this->language = $language;
    }
    
    /**
     * get render() output as string
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        $this->render();
        return ob_get_clean();
    }
    
    public function render()
    {
        if($this->script != null)
        {
        	printf(
                '<script type="text/%s" id="_%s">%s</script>'
                ,$this->language
                ,$this->id
                ,$this->script
            );
        }
    }
    
    public function run()
    {
    }
    /**
     * return ID of primary editable element or null 
     *
     * @return string|null
     */
    public function getPrimaryInputID()
    {
        return "_".$this->id;
    }
}
?>