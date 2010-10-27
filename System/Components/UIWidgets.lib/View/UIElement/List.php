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
class View_UIElement_List extends _View_UIElement 
{
	const CLASS_NAME = "View_UIElement_List";
	const CONFIRM = true;
	const DENY = false;
	
	private $ID;
	private $items;
	private $title;
	private $itemTag;
	private $wrapTag;
	private $autotranslate = true;
	
	public function __construct(array $items = array(), $itemTag = 'p', $wrapTag = null, $title = null)
	{		
		$this->ID = ++parent::$CurrentWidgetID;
		$this->items = $items;
		$this->title = $title;
	    $this->itemTag = $itemTag;
	    $this->wrapTag = $wrapTag;
	}

	public function add($item)
	{
	    $this->items[] = $item;
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
	    $itemTPL = empty($this->itemTag) ? "%s%s%s\n" : "<%s>%s</%s>\n";
	    if($this->title != null)
	    {
	        printf('<h3>%s</h3>', $this->autotranslate ? (SLocalization::get($this->title)) : $this->encode($this->title));
	    }
	    if($this->wrapTag != null)
	    {
	        printf("<%s class=\"%s\">", $this->encode($this->wrapTag), self::CLASS_NAME);
	    }
	    foreach ($this->items as $item) 
	    {
	    	printf($itemTPL, $this->itemTag, strval($item), $this->itemTag);
	    }
	    if($this->wrapTag != null)
	    {
	        printf('</%s>', $this->encode($this->wrapTag));
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