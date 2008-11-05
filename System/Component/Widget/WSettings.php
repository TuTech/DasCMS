<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 23.03.2008
 * @license GNU General Public License 3
 */
class WSettings extends BWidget implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * get category of this widget
	 * @return string
	 */
	public function getCategory()
	{
		return 'Settings';
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Search settings';
	}
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public function supportsObject($object)
	{
		//@todo remove chimera
		return ($object !== null && ($object instanceof BContent));
	}
	
	public function __construct($target)
	{
		$this->targetObject = $target;
		if(RSent::has('WSearch-Tags'))
		{
			$tagstr = RSent::get('WSearch-Tags');
			$chk = RSent::get('WSearch-Tags-chk');
			if($chk != md5($tagstr)) 
			{
				$this->targetObject->Tags = $tagstr;
			}
		}
		if(RSent::has('WSearch-PubDate'))
		{
			$dat = RSent::get('WSearch-PubDate');
			$chk = $this->targetObject->PubDate;
			if($chk != $dat) 
			{
				$this->targetObject->PubDate = $dat;
			}
		}
		$desc = RSent::get('WSearch-Desc');
		if(RSent::has('WSearch-Desc') && $desc != $this->targetObject->Description)
		{
			$this->targetObject->Description = $desc;
		}
	}
	
	public function __toString()
	{
		$tags = $this->targetObject->Tags;
		$tagstr = (is_array($tags)) ? implode(', ', $tags) : '';
		$html = '<div id="WSearch">';
		$html .= "<strong><label for=\"WSearch-PubDate\">PubDate</label></strong>";
		$pubDate = $this->targetObject->PubDate;
		$html .= sprintf('<input type="text" onfocus="this.select();" id="WSearch-PubDate" name="WSearch-PubDate" value="%s" />', (is_numeric($pubDate) && !empty($pubDate))? date('r', $this->targetObject->PubDate) : '');
		
		$html .= "<br /><strong><label for=\"WSearch-Tags\">Tags</label></strong>";
		$html .= sprintf('<textarea id="WSearch-Tags" name="WSearch-Tags">%s</textarea>', htmlentities($tagstr, ENT_QUOTES, 'utf-8'));
		$html .= sprintf('<input type="hidden" class="hidden" name="WSearch-Tags-chk" value="%s" />', md5($tagstr));
		if(get_class($this->targetObject) != 'CPage')
		{
			$html .= "<br /><strong>Description</strong>";
			$html .= sprintf('<textarea id="WSearch-Desc" name="WSearch-Desc">%s</textarea>', htmlentities($this->targetObject->Description, ENT_QUOTES, 'utf-8'));
		}
		$html .= "<br /><strong>Meta</strong>";
		$html .= '<table border="0">';
		$meta = array('Alias', 'PubDate','ModifyDate','ModifiedBy', 'CreateDate', 'CreatedBy', 'Size');
		foreach ($meta as $key) 
		{
		    $val = '-';
		    if(isset($this->targetObject->{$key}) && strlen($this->targetObject->{$key}) > 0) 
		    {
		        if(substr($key,-4) == 'Date') 
		        {
		            $val = date('r',$this->targetObject->{$key});
		        }
		        elseif(substr($key,-4) == 'Size')
		        {
		            $val = DFileSystem::formatSize($this->targetObject->{$key});
		        }
		        else
		        {
		            $val = htmlentities($this->targetObject->{$key}, ENT_QUOTES, 'UTF-8');
		        }
		    }
			$html .= sprintf(
				"<tr><th>%s</th><td>%s</td></tr>\n"
				, $key
				, $val
			);
		}
		
		$html .= '</table>';
		//		if($this->targetObject instanceof BContent)
//		{
//			$html .= "<br /><strong><label for=\"WSearch-Alias\">Current Alias</label></strong>";
//			$html .= sprintf('<input type="text" id="WSearch-Alias"readonly="readonly" value="%s" />'
//				, SAlias::alloc()->init()->prepareForURL($this->targetObject)); 
//		}
		
		$html .= '</div>';
		
		//@todo tag autocomplete
		
		return $html;
	}
}
?>