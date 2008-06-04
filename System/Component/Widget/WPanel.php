<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.05.2008
 * @license GNU General Public License 3
 */
class WPanel extends BWidget 
{
	const CLASS_NAME = "WPanel";
	
	const CENTER = 0;
	const NORTH = 1;
	const EAST = 2;
	const SOUTH = 3;
	const WEST = 4;
	const NORTHWEST = 5;
	const NORTHEAST = 6;
	const SOUTHEAST = 7;
	const SOUTHWEST = 8;
	
	const HORIZONTAL = 0;
	const VERTICAL = 1;
	
	private $orientation = 1;
	private $children = array();
	private $ID;
	
	public function __construct($target = null)
	{
		$this->ID = ++parent::$CurrentWidgetID;
		for($i = 0; $i <= 8; $i++)
		{
			$this->children[$i] = array();
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
	/**
	 * generate output
	 *
	 * @param int $field
	 * @param int $colspan
	 * @param int $rowspan
	 */
	protected function renderField($field, $colspan = 0, $rowspan = 0)
	{
		printf(
			"\t\t<td class=\"%s-%s\"%s%s>\n"
			,self::CLASS_NAME
			,$field
			,($colspan > 0) ? ' colspan="'.$colspan.'"' : ''
			,($rowspan > 0) ? ' rowspan="'.$rowspan.'"' : ''
		);
		foreach ($this->children[$field] as $widget) 
		{
			$widget->render();
		}
		echo "\t\t</td>\n";	
	}
	
	public function render()
	{
		$displayTop = (count($this->children[self::NORTHWEST]) 
			+ count($this->children[self::NORTH])
			+ count($this->children[self::NORTHEAST])) > 0;
		$displayBottom = (count($this->children[self::SOUTHWEST]) 
			+ count($this->children[self::SOUTH])
			+ count($this->children[self::SOUTHEAST])) > 0;
		$displayLeft = (count($this->children[self::NORTHWEST]) 
			+ count($this->children[self::WEST])
			+ count($this->children[self::SOUTHWEST])) > 0;
		$displayRight = (count($this->children[self::NORTHEAST]) 
			+ count($this->children[self::EAST])
			+ count($this->children[self::SOUTHEAST])) > 0;
		$displayTopLeft =  $displayLeft && count($this->children[self::NORTHWEST]) > 0;
		$displayTopRight =  $displayRight && count($this->children[self::NORTHEAST]) > 0;
		$displayBottomLeft =  $displayLeft && count($this->children[self::SOUTHWEST]) > 0;
		$displayBottomRight =  $displayRight && count($this->children[self::SOUTHEAST]) > 0;
		
		//render and echo data
		echo "<table border=\"1\" cellspacing=\"0\" id=\"", $this->ID, "\" class=\"",self::CLASS_NAME,"\">\n";
		//@todo remove copy paste code
		
		//top
		if($displayTop)
		{
			echo "\t<tr>\n";
			if($displayTopLeft)
			{
				$this->renderField(self::NORTHWEST);
			}
			elseif($displayLeft)
			{
				$rowspan = ($this->orientation == self::VERTICAL) 
					? 1 + $displayTop + $displayBottom - $displayBottomLeft - $displayTopLeft
					: 1;
				if($rowspan > 1)$this->renderField(self::WEST,0, $rowspan);
			}
			
			$colspan = 0;
			if((!$displayTopLeft || !$displayTopRight) && $this->orientation == self::HORIZONTAL)
			{
				$colspan = 1 + $displayTopLeft + $displayTopRight;
			}
			$this->renderField(self::NORTH, $colspan);
			
			if($displayTopRight)
			{
				$this->renderField(self::NORTHEAST);
			}
			elseif($displayRight)
			{
				$rowspan = ($this->orientation == self::VERTICAL) 
					? 1 + $displayTop + $displayBottom - $displayBottomRight - $displayTopRight
					: 1;
				if($rowspan > 1)$this->renderField(self::EAST, 0, $rowspan);
			}
			
			echo "\t</tr>\n";
		}
		
		//mid
		echo "\t<tr>\n";
		if($displayLeft && ($displayTopLeft || !$displayTop))
		{
			$rowspan = ($this->orientation == self::VERTICAL) 
				? 1 + $displayTop + $displayBottom - $displayBottomLeft - $displayTopLeft
				: 1;
			$this->renderField(self::WEST,0, $rowspan);
		}//else grow north|west
		
		$this->renderField(self::CENTER);
		
		if($displayRight  && ($displayTopRight || !$displayTop))
		{
			$rowspan = ($this->orientation == self::VERTICAL) 
				? 1 + $displayTop + $displayBottom - $displayBottomRight - $displayTopRight
				: 1;
			$this->renderField(self::EAST, 0, $rowspan);
		}//else grow north|west
		echo "\t</tr>\n";
		
		//bottom
		if($displayBottom)
		{
			echo "\t<tr>\n";
			if($displayBottomLeft)
			{
				$this->renderField(self::SOUTHWEST);
			}//else grow north|west
			
			$colspan = 0;
			if((!$displayBottomLeft || !$displayBottomRight) && $this->orientation == self::HORIZONTAL)
			{
				$colspan = 1 + $displayBottomLeft + $displayBottomRight;
			}
			$this->renderField(self::SOUTH, $colspan);
			
			if($displayBottomRight)
			{
				$this->renderField(self::SOUTHEAST);
			}//else grow north|west
			echo "\t</tr>\n";
		}
		echo "</table>\n";
	}
	/**
	 * call run on child elements
	 */
	public function run()
	{
		for($i = 0; $i <= 8; $i++)
		{
			foreach ($this->children[$i] as $widget) 
			{
				$widget->run();
			}
		}
	}
	/**
	 * add child 
	 *
	 * @param BWidget $widget
	 * @param int $orientation
	 */
	public function add(BWidget $widget, $orientation = self::CENTER)
	{
		if(!array_key_exists($orientation, $this->children))
		{
			throw new OutOfBoundsException();
		}
		$this->children[$orientation][] = $widget;
	}
	
	public function setOrientation($orientation)
	{
		$this->orientation = ($orientation == self::VERTICAL) ? self::VERTICAL : self::HORIZONTAL;
	}
}

?>