<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.05.2008
 * @license GNU General Public License 3
 */
class WLabel extends BWidget 
{
	private $ID;
	private $forTarget = null;
	private $text = null;
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
		echo "<label", ($this->forTarget != null ? ' for="'.$this->forTarget.'"':''),'>';
		echo htmlentities(($this->target != null && $this->text == null) 
			? $this->target->Title 
			: $this->text,ENT_QUOTES, 'UTF-8');
		echo "</label>\n";
	}
	
	public function run(){}
	
	public function setTarget(BWidget $widget)
	{
		$this->forTarget = $widget->getPrimaryInputID();
	}
	
	public function setText($text)
	{
		$this->text = $text;
	}
	
	public function getText()
	{
		return ($this->target != null && $this->text == null) 
			? $this->target->Title 
			: $this->text;
	}
}
?>