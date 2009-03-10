<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-03-23
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WTagPermissions extends BWidget implements ISidebarWidget 
{
	private $target;
	private $type;
	
    /**
	 * @return string
	 */
	public function getName()
	{
		return 'tag_permissions';
	}
	
	public function getIcon()
	{
	    return new WIcon('protect','',WIcon::SMALL,'action');
	}
	
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && !$sidepanel->isTargetObject()
	        && (
	            $sidepanel->getTargetMimeType() == 'cms/user'
				|| $sidepanel->getTargetMimeType() == 'cms/group'
			)
            && $sidepanel->isMode(WSidePanel::PERMISSIONS)
	    );
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
	    $this->type = $sidepanel->getTargetMimeType();
	    $this->target = $sidepanel->getTarget();
	    
	    //DO SAVE
	    if(RSent::hasValue('WTagPermissions_target'))
	    {
	        $name = RSent::get('WTagPermissions_target', 'utf-8');
	        $type = RSent::get('WTagPermissions_type', 'utf-8');
	        $tags = STagPermissions::getProtectedTags();
	        $setTags = array();
	        foreach ($tags as $tag) 
	        {
	            $chksum = md5($tag);
	        	if(RSent::hasValue('WTagPermissions_'.$chksum))
	        	{
	        	    $setTags[] = $tag;
	        	}
	        }
	        if($type == 'cms/group')
	        {
	            STagPermissions::setGroupPermissions($name, $setTags);
	        }
	        else
	        {
	            STagPermissions::setUserPermissions($name, $setTags);
	        }
	    }
	}
	
	public function __toString()
	{
		$html = '<div id="WTagPermissions">';
		$html .= LGui::hiddenInput('WTagPermissions_target', $this->target);
		$html .= LGui::hiddenInput('WTagPermissions_type', $this->type);
		$tags = STagPermissions::getProtectedTags();
		$permitted = ($this->type == 'cms/user')
		    ? (STagPermissions::getUserPermissionTags($this->target))
		    : (STagPermissions::getGroupPermissionTags($this->target));
		$wt = new WTable(WTable::HEADING_TOP);
		$wt->addRow(array('', 'restricted_tags'));
		foreach ($tags as $tag) 
		{
		    $check = in_array($tag, $permitted) ? 'checked="checked" ' : '';
		    $chksum = md5($tag);
		    $wt->addRow(
		        array(
		            sprintf("<input type=\"checkbox\" %sname=\"WTagPermissions_%s\" id=\"WTagPermissions_%s\" />", $check, $chksum, $chksum),
		            sprintf("<label for=\"WTagPermissions_%s\">%s</label>", $chksum, htmlentities($tag, ENT_QUOTES, 'UTF-8'))
		        )
		    );
		}
		$html .= strval($wt);
		$html .= sprintf(
		    "<div class=\"WTagPermissions_controls\">".
		        "<a href=\"javascript:org.bambuscms.wtagpermissions.selectAll()\">%s</a>".
		        "<a href=\"javascript:org.bambuscms.wtagpermissions.selectNone()\">%s</a>".
	        "</div>"
			,SLocalization::get('select_all')
			,SLocalization::get('select_none')
        );
		$html .= '</div>';
		return $html;
	}
}
?>