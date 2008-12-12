<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.05.2008
 * @license GNU General Public License 3
 */
class WTextEditor extends BWidget 
{
	const CLASS_NAME = "WTextEditor";
	private $ID;
	private $target = null;
	
	public function __construct($target)
	{		
		$this->ID = ++parent::$CurrentWidgetID;
		if(is_object($target) && $target instanceof BContent)
		{
			$this->target = $target;
		}
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
		if($this->target != null)
		{
			echo "<textarea rows=\"10\" cols=\"60\" onmouseout=\"\$('_",$this->ID, "-ScrollPos').value=this.scrollTop;\" id=\"_",
				$this->ID,"\" name=\"",$this->target->Id,"-Content\" class=\"",self::CLASS_NAME,"\">";
			echo htmlentities($this->target->Content, ENT_QUOTES, 'UTF-8');
			echo "</textarea>\n";
			echo "<input type=\"hidden\" id=\"_",$this->ID,"-ScrollPos\" name=\"",$this->target->Id,"-Content-ScrollPos\" value=\"",
				htmlentities(RSent::get($this->target->Id."-Content-ScrollPos"), ENT_QUOTES, 'UTF-8')
			,"\" />\n";
			echo "<script type=\"text/javascript\">\$('_",$this->ID,"').scrollTop = $('_",$this->ID,"-ScrollPos').value;</script>\n";
		}
	}
	
	public function run()
	{
		if($this->target != null)
		{
			if(RSent::has($this->target->Id."-Content"))
			{
				//@todo check permissions 
				$this->target->Content = RSent::get($this->target->Id."-Content", 'utf-8');
			}
		}
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