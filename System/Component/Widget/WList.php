<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.05.2008
 * @license GNU General Public License 3
 */
class WList extends BWidget 
{
	const CLASS_NAME = "WList";
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
	    return htmlentities(mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1,UTF-8'), ENT_QUOTES, 'UTF-8');
	}

	public function render()
	{
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
	    	printf("<%s>%s</%s>\n", $this->itemTag, strval($item), $this->itemTag);
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