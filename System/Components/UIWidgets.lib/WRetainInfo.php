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
class WRetainInfo extends BWidget implements ISidebarWidget 
{
	private $targetObject = null;
	private static $retains = array();
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    $wanted = false;
	    $possible = (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(WSidePanel::RETAIN)
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
	    return new WIcon('retain','',WIcon::SMALL,'action');
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
	    ob_start();
	    $this->render();
	    $html = strval(ob_get_clean());
		return $html;
	}
	
	public function render()
	{
	    echo '<div id="WRetainInfo">';
	    if(count(self::$retains))
	    {
    	    printf('<dl><dt><label>%s</label></dt><dd><dl>', SLocalization::get('retained_by'));
    	    $i = 1;
    	    $currentClass = '';
    	    foreach(self::$retains as $alias => $data)
    	    {
    	        list($class, $title) = $data;
    	        if($currentClass != $class)
    	        {
    	            printf('<dt>%s</dt>', SLocalization::get($class));
    	            $currentClass = $class;
    	            $i = 1;
    	        }
    	        printf(
    	        	"<dd class=\"small-padding%s\" title=\"%s\">%s</dd>"
    	        	,($i++%2 ? '':  ' alt')
    	            ,htmlentities($alias, ENT_QUOTES, CHARSET)
    	        	,htmlentities($title, ENT_QUOTES, CHARSET)
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