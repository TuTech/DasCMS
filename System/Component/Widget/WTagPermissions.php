<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 23.03.2008
 * @license GNU General Public License 3
 */
class WTagPermissions extends BWidget implements ISidebarWidget 
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'tag_permissions';
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
	
	public function __construct(WSidePanel $sidepanel)
	{
	    //DO SAVE


//		$this->targetObject = $sidepanel->getTarget();
//		if(RSent::has('WSearch-WTagPermissions'))
//		{
//			$tagstr = RSent::get('WSearch-Tags');
//			$chk = RSent::get('WSearch-Tags-chk');
//			if($chk != md5($tagstr)) 
//			{
//				$this->targetObject->Tags = $tagstr;
//			}
//		}
//		if(RSent::has('WSearch-PubDate'))
//		{
//			$dat = RSent::get('WSearch-PubDate');
//			$chk = $this->targetObject->PubDate;
//			if($chk != $dat) 
//			{
//				$this->targetObject->PubDate = $dat;
//			}
//		}
//		$desc = RSent::get('WSearch-Desc');
//		if(RSent::has('WSearch-Desc') && $desc != $this->targetObject->Description)
//		{
//			$this->targetObject->Description = $desc;
//		}
	}
	
	public function __toString()
	{
		$html = '<div id="WTagPermissions">';
		$html .= '</div>';
		return $html;
	}
}
?>