<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-29
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WApplications extends BWidget 
{
	private $apps = array();
	
	public function __construct($target = '')//BObject $target
	{
		//the current app?
		$this->apps = SBapReader::getSharedInstance()->listAvailable();
		
		//read apps from dir
		
		//read apps xml
		
		//forek app display icon 
	}
	
	private function selectIcon($name, $active = true)
	{
		$parts = explode('-', $name);
		//specifyer in name
		$type = array_shift($parts);
		$path = SPath::SYSTEM_ICONS.'/'.($active ? 'large' : 'medium').'/'.strtolower($type).'s/';
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
			return './System/ClientData/Icons/'.($active ? '48x48' : '32x32').'/apps/'.$name.'.png';
		}
	}
	
	public function __toString()
	{
		$html = '<div id="'.get_class($this)."\"><table>\n<tr>";
		$sortHelp = array();
		foreach ($this->apps as $app => $meta) 
		{
		    $sortHelp[$app] = strtoupper(trim(SLocalization::get($meta['name'])));
		}
		asort($sortHelp, SORT_LOCALE_STRING);
		foreach ($sortHelp as $app => $sortHelp) 
		{
		    $meta = $this->apps[$app];
			$name = htmlentities(SLocalization::get($meta['name']), ENT_QUOTES, 'UTF-8');
			$html .= sprintf(
				"\t<td><a href=\"Management/?editor=%s\" onmousedown=\"return false;\" class=\"application%s\">\n".
					"\t\t<img src=\"%s\" alt=\"%s\" />\n".
					"\t\t<span class=\"application-info\">\n".
					"\t\t\t<span class=\"application-name\">%s</span>\n".
					"\t\t\t<span class=\"application-description\">%s</span>\n".
					"\t\t</span>\n".
					"\t</a></td>\n"
				,htmlentities($app, ENT_QUOTES, 'UTF-8')
				,($meta['active']) ? ' active' : ''
				,$this->selectIcon($meta['icon'], $meta['active'])
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