<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-05-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_Confirm extends _View_UIElement 
{
	const CLASS_NAME = "View_UIElement_Confirm";
	const CONFIRM = true;
	const DENY = false;
	
	private $ID;
	private $name;
	private $title;
	private $confirm;
	private $autotranslate = true;
	
	public function __construct($name, $title, $confirmed)
	{		
		$this->ID = ++parent::$CurrentWidgetID;
		$this->name = $name;
		$this->confirm = $confirmed;
		$this->title = $title;
	}

    public function setTitleTranslation($yn)
    {
        $this->autotranslate = $yn == true;
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
	
	private function encode($string)
	{
	    return String::htmlEncode(mb_convert_encoding($string, CHARSET, 'ISO-8859-1,UTF-8'));
	}

	public function render()
	{
	    $tpl = '<input class="%s" type="checkbox" name="%s"%s id="%s" /><label for="%s">%s</label>';
	    printf(
	        $tpl
	        ,self::CLASS_NAME
	        ,$this->encode($this->name)
	        ,$this->confirm ? ' checked="checked"' : ''
			,$this->getPrimaryInputID()
	        ,$this->getPrimaryInputID()
	        ,$this->autotranslate ? (SLocalization::get($this->title)) : $this->encode($this->title)    
	    );
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