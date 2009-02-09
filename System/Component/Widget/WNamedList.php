<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.05.2008
 * @license GNU General Public License 3
 */
class WNamedList extends BWidget 
{
	const CLASS_NAME = "WNamedList";
	const CONFIRM = true;
	const DENY = false;
	
	private $ID;
	private $items;
	private $title;
	private $autotranslate = true;
	
	public function __construct($title = null, array $items = array())
	{		
		$this->ID = ++parent::$CurrentWidgetID;
		$this->items = $items;
		$this->title = $title;
	}

	public function add($name, $value)
	{
	    $this->items[] = array($name, $value);
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
	    print("<dl>\n");
	    foreach($this->items as $kvArray)
	    {
	        printf("<dt>%s</dt><dd>%s</dd>\n", $this->autotranslate ? (SLocalization::get($kvArray[0])) : $kvArray[0], $kvArray[1]);
	    }
	    print("</dl>\n");
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