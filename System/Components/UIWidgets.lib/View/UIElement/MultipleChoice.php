<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-27
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_MultipleChoice extends _View_UIElement 
{
    const CLASS_NAME = "View_UIElement_MultipleChoice";
    const SELECT = 0;
    const RADIO = 1;
    
    private $ID;
    
    private $name;
    private $choices;
    private $selected;
    private $type;
    private $translateTitles = true;
    
    /**
     * @param string $name
     * @param array $choices
     * @param string $selected
     * @param int $type
     * @param boolean $autoTranslate
     */
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
        $this->selected = isset($this->choices[$choice]) ? $choice : null;
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
					,htmlentities(mb_convert_encoding($this->name, CHARSET, 'ISO-8859-1,UTF-8'), ENT_QUOTES, CHARSET)
					,self::CLASS_NAME
					,$this->ID
				);
        		foreach ($this->choices as $name => $title) 
        		{
        			printf(
        			    "\n\t<option value=\"%s\"%s>%s</option>"
						,$this->convent($name)
        			    ,($name == $this->selected && $this->selected !== null) ? ' selected="selected"' : ''
						,htmlentities(($this->translateTitles ? SLocalization::get($title) : mb_convert_encoding($title, CHARSET, 'ISO-8859-1,UTF-8')), ENT_QUOTES, CHARSET)
					);
        		}
        		printf("\n</select>");
        		break;
        	case self::RADIO:
        		printf(
    		        "\n<div id=\"%s_%s\">"
					,$this->convent($this->name)
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
        			    ,$this->convent($this->name)
						,$this->convent($name)
						//input name
						,$this->convent($this->name)
						//input value
						,$this->convent($name)
						//input selected
						,($name == $this->selected && $this->selected !== null) ? ' checked="checked"' : ''
						//label for
						,$this->convent($this->name)
						,$this->convent($name)
						//label text
						,($this->translateTitles ? SLocalization::get($title) : $this->convent($title))
					);
        		}
        		printf("\n</div>");
        		break;
        		
        	default:
        		;
        	break;
        }
        
    }
    
    private function convent($str)
    {
        return htmlentities(mb_convert_encoding($str, CHARSET, 'ISO-8859-1,UTF-8'), ENT_QUOTES, CHARSET);
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