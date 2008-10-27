<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 27.10.2008
 * @license GNU General Public License 3
 */
class WMultipleChoice extends BWidget 
{
    const CLASS_NAME = "WMultipleChoice";
    const SELECT = 0;
    const RADIO = 1;
    
    private $ID;
    
    private $name;
    private $choices;
    private $selected;
    private $type;
    private $translateTitles = true;
    
    public function __construct($name, array $choices, $selected, $type = self::SELECT, $autoTranslate = true)
    {       
        $this->ID = ++parent::$CurrentWidgetID;
        $this->name = $name;
        $this->choices = $choices;
        $this->setSelected($selected);
        $this->type = $type;
        $this->setTranslateTitles($autoTranslate);
    }

    public function setSelected($choice)
    {
    	$ck = array_keys($this->choices);
        $this->selected = (array_key_exists($choice, $this->choices)) ? $choice : null;
    }
    
    public function setTranslateTitles($yn)
    {
        $this->translateTitles = $yn == true;
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
        switch ($this->type) 
        {
        	case self::SELECT:
        		printf(
    		        "\n<select name=\"%s\" id=\"%s_%s\">"
					,htmlentities(mb_convert_encoding($this->name, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
					,self::CLASS_NAME
					,$this->ID
				);
        		foreach ($this->choices as $name => $title) 
        		{
        			printf(
        			    "\n\t<option value=\"%s\"%s>%s</option>"
						,htmlentities(mb_convert_encoding($name, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
        			    ,($name === $this->selected) ? ' selected="selected"' : ''
						,htmlentities(mb_convert_encoding(($this->translateTitles ? SLocalization::get($title) : $title), 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
					);
        		}
        		printf("\n</select>");
        		break;
        	case self::RADIO:
        		printf(
    		        "\n<div id=\"%s_%s\">"
					,htmlentities(mb_convert_encoding($this->name, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
					,self::CLASS_NAME
					,$this->ID
				);
        		foreach ($this->choices as $name => $title) 
        		{
        			printf(
        			    "\n\t<div class=\"%s_choice\">\n\t\t<input type=\"radio\" id=\"%s_%s\" name=\"%s\" value=\"%s\"%s />\n\t\t<label for=\"%s_%s\">%s</label>\n\t</div>"
						//div class
						,self::CLASS_NAME						
					    //input id
        			    ,htmlentities(mb_convert_encoding($this->name, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
						,htmlentities(mb_convert_encoding($name, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
						//input name
						,htmlentities(mb_convert_encoding($this->name, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
						//input value
						,htmlentities(mb_convert_encoding($name, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
						//input selected
						,($name === $this->selected) ? ' checked="checked"' : ''
						//label for
						,htmlentities(mb_convert_encoding($this->name, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
						,htmlentities(mb_convert_encoding($name, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
						//label text
						,htmlentities(mb_convert_encoding(($this->translateTitles ? SLocalization::get($title) : $title), 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8')
					);
        		}
        		printf("\n</div>");
        		break;
        		
        	default:
        		;
        	break;
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
        return null;
    }
}
?>