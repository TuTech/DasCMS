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
class View_UIElement_RetainInfo extends _View_UIElement implements ISidebarWidget 
{
	private $targetObject = null;
	private static $retains = array();
	/**
	 * @return array
	 */
	public static function isSupported(View_UIElement_SidePanel $sidepanel)
	{
	    $wanted = false;
	    $possible = (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(View_UIElement_SidePanel::RETAIN)
	    );
	    if($possible)
	    {
			$RelCrtl = Controller_ContentRelationManager::getInstance();
			self::$retains = $RelCrtl->getRetainees($sidepanel->getTarget()->getAlias(), true);
	        $wanted = count(self::$retains) > 0;
	    }
	    return $wanted && $possible;
	}
	
	public function getName()
	{
	    return 'retain_info';
	}
	
	public function getIcon()
	{
	    return new View_UIElement_Icon('retain','',View_UIElement_Icon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(View_UIElement_SidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
	}
	
	public function __toString()
	{
	    ob_start();
	    $this->render();
	    $html = strval(ob_get_clean());
		return $html;
	}
	
	public function render()
	{
	    echo '<div id="View_UIElement_RetainInfo">';
	    if(count(self::$retains))
	    {
    	    printf('<dl><dt><label>%s</label></dt><dd><dl>', SLocalization::get('retained_by'));
    	    $i = 1;
    	    $currentClass = '';
    	    foreach(self::$retains as $nr => $data)
    	    {
    	        list($alias, $class, $title) = $data;
    	        if($currentClass != $class)
    	        {
    	            printf('<dt>%s</dt>', SLocalization::get($class));
    	            $currentClass = $class;
    	            $i = 1;
    	        }
    	        printf(
    	        	"<dd class=\"small-padding%s\" title=\"%s\">%s</dd>"
    	        	,($i++%2 ? '':  ' alt')
    	            ,String::htmlEncode($alias)
    	        	,String::htmlEncode($title)
    	        	);
    	    }
    	    echo '</dl></dd></dl>';
	    }
	    echo '</div>';
	}
	
	public function associatedJSObject()
	{
	    return null;
	}
}
?>