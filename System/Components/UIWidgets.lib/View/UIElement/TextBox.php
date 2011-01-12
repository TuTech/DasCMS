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
class View_UIElement_TextBox extends _View_UIElement 
{
	const CLASS_NAME = "View_UIElement_TextBox";
	
	const TEXT = 'Text';
	const PASSWORD = 'Password';
	const MULTILINE = 'MultilineText';
	const NUMERIC = 'Numeric';
	const DATE = 'Date';
	const TIME = 'Time';
	const DATETIME = 'DateTime';
	
	private $id;
	private $name;
	private $type;
	private $value;
	private $label;
	private $autotranslate;
	
	public function __construct($name, $value, $type, $label = null, $autotranslate = true)
	{		
		$this->id = ++parent::$CurrentWidgetID;
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;
		$this->label = $label;
		$this->autotranslate = $autotranslate;
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
	    return String::htmlEncode(mb_convert_encoding($string, CHARSET, 'UTF-8,ISO-8859-1'));
	}

	public function render()
	{
	    switch ($this->type) 
	    {
	    	case self::DATE:
	    	    $val = is_numeric($this->value) ? date('Y/m/d', $this->value) : $this->value;
	    	case self::TIME:
	    	    $val = is_numeric($this->value) ? date('H:i:s', $this->value) : $this->value;
	    	case self::DATETIME:
	    	    $val = is_numeric($this->value) ? date('c', $this->value) : $this->value;
	    	    $tpl = '<input class="%s %s-%s" type="text" name="%s" id="%s" value="%s" />';
	    		break;
	    	case self::TEXT:
	    	case self::NUMERIC:
	    	    $val = $this->value;
	    	    $tpl = '<input class="%s %s-%s" type="text" name="%s" id="%s" value="%s" />';
	    		break;
	    	case self::PASSWORD:
	    	    $val = $this->value;
	    	    $tpl = '<input class="%s %s-%s" type="password" name="%s" id="%s" value="%s" />';
	    		break;
    		case self::MULTILINE:
    		    $val = $this->value;
	    	    $tpl = '<textarea class="%s %s-%s" type="text" name="%s" id="%s">%s</textarea>';
	    		break;
	    	default:
	    		throw new XArgumentException('unknown type');
	    }
	    printf('<table class="%s-wrapper"><tr>', self::CLASS_NAME);
	    if($this->label != null)
	    {
	        printf(
	            '<th><label for="%s" class="%s-label">%s</label></th>'
	            ,$this->getPrimaryInputID()
	            ,self::CLASS_NAME
	            ,$this->autotranslate ? (SLocalization::get($this->label)) : $this->encode($this->label)
	        );
	    }
	    printf(
	        '<td>'.$tpl.'</td>'
	        ,self::CLASS_NAME
	        ,self::CLASS_NAME
	        ,$this->type
	        ,$this->encode($this->name)
	        ,$this->getPrimaryInputID()
	        ,$this->encode($val)
	    );
	    echo '</tr></table>';
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