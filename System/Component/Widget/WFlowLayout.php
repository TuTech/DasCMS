<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-17
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WFlowLayout extends BWidget 
{
    private $id;
    //display 
    private $title = null;
    private $translateHeadings = true;
    
    //data
    private $data = array();
        
    //Alteration
    private $cssClasses = '';
    
    public function setData(array $data)
    {
        $this->data = $data;
    }
    
    public function addItem($item)
    {
        $this->data[] = $item;
    }
    
    public function setHeaderTranslation($yn)
    {
        $this->translateHeadings = $yn == true;
    }
    
    public function setAdditionalCSSClasses($cssClasses)
    {
        $this->cssClasses = $cssClasses;
    }
    
    public function setTitle($title = null, $translate = true)
    {
        if($title == null)
        {
            $this->title = null;
        }
        else
        {
            $this->title = $translate ? SLocalization::get($title) : $title;
        }
    }
    
    public function __construct($title = null)
    {
        $this->setTitle($title);
        $this->id = ++parent::$CurrentWidgetID;
    }
    
    public function __toString()
    {
        ob_start();
        $this->render();
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
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
        if($this->title != null)
        {
            printf("<h3>%s</h3>\n", $this->title);
        }
        printf("<div class=\"WFlowLayout\" id=\"WFlowLayout_%s\">\n", $this->id);
        foreach ($this->data as $item) 
        {
        	printf("\t<div class=\"WFlowLayoutItem %s\">\n\t\t", $this->cssClasses);
        	if($item instanceof BWidget)
        	{
        	    $item->render();
        	}
        	else
        	{
        	    echo strval($item);
        	}
        	echo "\n\t</div>\n";
        }
        print("\t<br class=\"WFlowLayoutEnd\" />\n</div>\n");
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