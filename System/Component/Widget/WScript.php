<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.08.2008
 * @license GNU General Public License 3
 */
class WScript extends BWidget 
{
    const CLASS_NAME = "WScript";
    private $ID;
    private $language = 'javascript';
    private $script;
    public function __construct($target)
    {       
        $this->ID = ++parent::$CurrentWidgetID;
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
                ,$this->ID
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
        return "_".$this->ID;
    }
}
?>