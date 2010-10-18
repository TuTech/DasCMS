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
class View_UIElement_Applications extends _View_UIElement 
{
	private $apps = array();
	
	public function __construct($target = '')
	{
		//the current app?
		$this->apps = SApplication::listApplications();
		
		//read apps from dir
		
		//read apps xml
		
		//forek app display icon 
	}
	
	private function selectIcon($name, $active = true)
	{
		$icon = null;
		$parts = explode('-', $name);
		//specifyer in name
		$type = array_shift($parts);
		$path = Core::PATH_SYSTEM_ICONS.'/'.($active ? 'large' : 'medium').'/'.strtolower($type).'s/';
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
					$icon = $file;
					break;
				}
				array_pop($parts);
			}
		}
		else
		{
			$icon = './System/ClientData/Icons/'.($active ? '48x48' : '32x32').'/apps/'.$name.'.png';
		}
		if($active)View_UIElement_Header::setIcon($icon);
		return $icon;
	}
	
	public function __toString()
	{
		$html = '<div id="'.get_class($this)."\">";
		
		//grouping by purpose
		$groupHelp = array();
		foreach ($this->apps as $item => $atts)
		{
		    $groupHelp[$atts['purpose']] = SLocalization::get($atts['purpose']);
		}
		asort($groupHelp, SORT_LOCALE_STRING);
		$groupHelp = array_reverse($groupHelp, true);
		$sortHelp = array();
		foreach ($this->apps as $app => $meta) 
		{
		    $sortHelp[$app] = strtoupper(trim(SLocalization::get($meta['name'])));
		}
		$html .= '<table><tr>';
		foreach ($groupHelp as $group => $locale)
		{
		    $html .= '<th class="appGroup appGroup-'.htmlentities($group, ENT_QUOTES, CHARSET)."\" title=\"".$locale."\"><span>&nbsp;</span></th>";
		    
		
		    
    		//asort($sortHelp, SORT_LOCALE_STRING);
    		foreach ($sortHelp as $app => $sortHelper) 
    		{
    		    if($this->apps[$app]['purpose'] == $group)
    		    {
        		    $meta = $this->apps[$app];
        			$name = htmlentities(SLocalization::get($meta['name']), ENT_QUOTES, CHARSET);
        			$html .= sprintf(
        				"\t<td><a href=\"Management/?editor=%s\" onmousedown=\"return false;\" class=\"application%s\">\n".
        					"\t\t<img src=\"%s\" alt=\"%s\" />\n".
        					"\t\t<span class=\"application-info\">\n".
        					"\t\t\t<span class=\"application-name\">%s</span>\n".
        					"\t\t\t<span class=\"application-description\">%s</span>\n".
        					"\t\t</span>\n".
        					"\t</a></td>\n"
        				,htmlentities($app, ENT_QUOTES, CHARSET)
        				,($meta['active']) ? ' active' : ''
        				,$this->selectIcon($meta['icon'], $meta['active'])
        				,$name
        				,$name
        				,SLocalization::get($meta['desc'])
        			
        			);
    		    }
    		}
    		
		}$html .= '</tr></table></div>';
		#$html .= '</div>';
		return $html;
	}
	
}
?>