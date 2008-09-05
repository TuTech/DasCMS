<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.05.2008
 * @license GNU General Public License 3
 */
class WTextBox extends BWidget 
{
	const CLASS_NAME = "WTextBox";
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
			echo "<input type=\"text\" id=\"_",$this->ID,"\" name=\"",$this->target->Id,"-Title\" value=\"",
				htmlentities($this->target->Title, ENT_QUOTES, 'UTF-8')
			,"\" class=\"",self::CLASS_NAME,"\" />\n";
		}
	}
	
	public function run()
	{
		if($this->target != null)
		{
			if(RSent::has($this->target->Id."-Title"))
			{
				//@todo check permissions 
				$this->target->Title = RSent::get($this->target->Id."-Title");
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