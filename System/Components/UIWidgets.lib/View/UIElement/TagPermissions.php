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
class View_UIElement_TagPermissions extends _View_UIElement implements ISidebarWidget 
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
	    return new View_UIElement_Icon('protect','',View_UIElement_Icon::SMALL,'action');
	}
	
	public static function isSupported(View_UIElement_SidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && !$sidepanel->isTargetObject()
	        && (
	            $sidepanel->getTargetMimeType() == 'cms/user'
				|| $sidepanel->getTargetMimeType() == 'cms/group'
			)
            && $sidepanel->isMode(View_UIElement_SidePanel::PERMISSIONS)
	    );
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(View_UIElement_SidePanel $sidepanel)
	{
	    $this->type = $sidepanel->getTargetMimeType();
	    $this->target = $sidepanel->getTarget();
	    
	    //DO SAVE
	    if(RSent::hasValue('View_UIElement_TagPermissions_target'))
	    {
	        $name = RSent::get('View_UIElement_TagPermissions_target', CHARSET);
	        $type = RSent::get('View_UIElement_TagPermissions_type', CHARSET);
	        $tags = Controller_Permissions_Tag::getProtectedTags();
	        $setTags = array();
	        foreach ($tags as $tag) 
	        {
	            $chksum = md5($tag);
	        	if(RSent::hasValue('View_UIElement_TagPermissions_'.$chksum))
	        	{
	        	    $setTags[] = $tag;
	        	}
	        }
	        if($type == 'cms/group')
	        {
	            Controller_Permissions_Tag::setGroupPermissions($name, $setTags);
	        }
	        else
	        {
	            Controller_Permissions_Tag::setUserPermissions($name, $setTags);
	        }
	    }
	}
	
	public function __toString()
	{
		$html = '<div id="View_UIElement_TagPermissions">';
		$html .= '<input type="hidden" name="View_UIElement_TagPermissions_target" value="'.$this->target.'" />';
		$html .= '<input type="hidden" name="View_UIElement_TagPermissions_type" value="'.$this->type.'" />';
		$tags = Controller_Permissions_Tag::getProtectedTags();
		$permitted = ($this->type == 'cms/user')
		    ? (Controller_Permissions_Tag::getUserPermissionTags($this->target))
		    : (Controller_Permissions_Tag::getGroupPermissionTags($this->target));
		$wt = new View_UIElement_Table(View_UIElement_Table::HEADING_TOP);
		$wt->addRow(array('', 'restricted_tags'));
		foreach ($tags as $tag) 
		{
		    $check = in_array($tag, $permitted) ? 'checked="checked" ' : '';
		    $chksum = md5($tag);
		    $wt->addRow(
		        array(
		            sprintf("<input type=\"checkbox\" %sname=\"View_UIElement_TagPermissions_%s\" id=\"View_UIElement_TagPermissions_%s\" />", $check, $chksum, $chksum),
		            sprintf("<label for=\"View_UIElement_TagPermissions_%s\">%s</label>", $chksum, htmlentities($tag, ENT_QUOTES, CHARSET))
		        )
		    );
		}
		$html .= strval($wt);
		$html .= sprintf(
		    "<div class=\"View_UIElement_TagPermissions_controls\">".
		        "<a href=\"javascript:org.bambuscms.wtagpermissions.selectAll()\">%s</a>".
		        "<a href=\"javascript:org.bambuscms.wtagpermissions.selectNone()\">%s</a>".
	        "</div>"
			,SLocalization::get('select_all')
			,SLocalization::get('select_none')
        );
		$html .= '</div>';
		return $html;
	}
	
	public function associatedJSObject()
	{
	    return null;
	}
}
?>