<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 29.04.2008
 * @license GNU General Public License 3
 */
class WContentLookup extends BWidget implements ISidebarWidget  
{
    const CLASS_NAME = 'WContentLookup';
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
		return $sidepanel->isMode(WSidePanel::CONTENT_LOOKUP);
	}
	
	public function getName()
	{
	    return 'content_lookup';
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
	}
	
	public function __toString()
	{
		$html = '<strong>All Contents</strong>';
		$html .= '<div id="WCLSearchBox">'.
		            '<input type="text" id="WContentLookupFilter" onchange="org.bambuscms.wcontentlookup.filter();" '.
		            'onkeyup="org.bambuscms.wcontentlookup.filter();" /></div>';
		
		try
		{
		    $res = QWContentLookup::fetchContentList();
			$rows = $res->getRowCount()+5;
				
			$html .= '<select id="WContentLookup" '.
			            'onclick="insertMedia(\'content\', this.options[this.selectedIndex].value, '.
			            'this.options[this.selectedIndex].text)" size="'.$rows.'">';
			
			$lastMan = null;
			while($erg = $res->fetch())
			{
				list($ctype, $alias, $ttl, $pub) = $erg;
				if($ctype != $lastMan)
				{
					if($lastMan != null)  $html .= '</optgroup>';
					$html .= '<optgroup label="'. substr($ctype,1).'">';
					$lastMan = $ctype;
				}
				$pub = strtotime($pub);
				$class = 'unpublished';
				$title = 'Not public';
				if($pub > 0){
					$class = 'published';
					$title = 'Published: '.date('r',$pub);
				}
				if($pub > time()){
					$class = 'publicationScheduled';
					$title = 'Not yet public ('.date('r',$pub).')';
				}
				$html .= '<option value="'.$alias.'" class="'.$class.'" title="'.$title.'">'.
								htmlentities($ttl, ENT_QUOTES, 'UTF-8').' ('.htmlentities($alias, ENT_QUOTES, 'UTF-8').')'
						.'</option>';
			}
			$res->free();
			if($lastMan != null)  $html .= '</optgroup>';
			
			$html .= '</select>';
		}
		catch (Exception $e)
		{
		    $html .= sprintf(
		        '<div>Ex @ %s line %s<b>%s: %s</b></div>'
				,$e->getFile()
		        ,$e->getLine()
		        ,$e->getCode()
		        ,$e->getMessage()
			);
		}
		return $html;
	}
}
?>