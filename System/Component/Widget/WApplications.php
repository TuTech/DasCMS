<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 29.04.2008
 * @license GNU General Public License 3
 */
class WApplications extends BWidget 
{
	private $apps = array();
	
	public function __construct($target)//BObject $target
	{
		//the current app?
		$this->apps = SApplication::alloc()->init()->listAvailable();
		
		//read apps from dir
		
		//read apps xml
		
		//forek app display icon 
	}
	
	private function selectIcon($name)
	{
		$parts = explode('-', $name);
		//specifyer in name
		$type = array_shift($parts);
		$path = SPath::SYSTEM_ICONS.'/large/'.strtolower($type).'s/';
		if(ctype_alpha($type) && is_dir($path))
		{
			//valid path
			$suffix = '.png';
			while(count($parts) > 0)
			{
				//try to find most specific icon
				$file = $path.implode('-',$parts).$suffix;
				if(file_exists($file))
				{
					return $file;
				}
				array_pop($parts);
			}
		}
		else
		{
			return './System/Icons/48x48/apps/'.$name.'.png';
		}
	}
	
	public function __toString()
	{
		$html = '<div id="'.get_class($this)."\"><table>\n<tr>";
		asort($this->apps, SORT_LOCALE_STRING);
		foreach ($this->apps as $app => $meta) 
		{
			$name = htmlentities(SLocalization::get($meta['name']), ENT_QUOTES, 'UTF-8');
			$html .= sprintf(
				"\t<td><a href=\"Management/?editor=%s&amp;tab=%s\" class=\"application%s\">\n".
					"\t\t<img src=\"%s\" alt=\"%s\" title=\"%s\"/>\n".
					"\t\t<span class=\"application-name\">%s</span>\n".
					"\t\t<span class=\"application-description\">%s</span>\n".
				"\t</a></td>\n"
				,htmlentities($app, ENT_QUOTES, 'UTF-8')
				,($meta['active']) ? $meta['active'] : ''
				,($meta['active']) ? ' active' : ''
				,$this->selectIcon($meta['icon'])
				,$name
				,$name
				,$name
				,SLocalization::get($meta['desc'])
			
			);
		}
		$html .= '</tr></table></div>';
		return $html;
	}
	
}
?>