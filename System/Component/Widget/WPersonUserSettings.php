<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-02-24
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WPersonUserSettings extends BWidget implements ISidebarWidget 
{
    /**
     * @var CUser
     */
	private $targetObject = null;
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(WSidePanel::PERMISSIONS)
	        && get_class($sidepanel->getTarget()) == 'CPerson'
	    );
	}
	
	public function getName()
	{
	    return 'person_user_settings';
	}
	
	public function getIcon()
	{
	    return new WIcon('access','',WIcon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
	}
	
	public function __toString()
	{
		$html = '<div id="WPersonUserSettings">';
		$Items = new WNamedList();
		$Items->setTitleTranslation(false);
		
		if($this->targetObject->hasLogin())
		{
    		$Items->add(
    		    sprintf("<label>%s</label>", SLocalization::get('login_name')),
    		    $this->targetObject->getLoginName()
		    );		
		}
		else
		{
    		$Items->add(
    		    sprintf("<label>%s</label>", SLocalization::get('login')),
    		    SLocalization::get('no_account_created_for_this_user')
		    );
		}
	    $html .= $Items;
		$html .= '</div>';
		return $html;
	}
}
?>