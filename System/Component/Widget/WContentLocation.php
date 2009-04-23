<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-23
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WContentLocation extends BWidget implements ISidebarWidget 
{
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
	        && $sidepanel->isMode(WSidePanel::PROPERTY_EDIT)
	    );
	}
	
	public function getName()
	{
	    return 'content_location';
	}
	
	public function getIcon()
	{
	    return new WIcon('worldmap','',WIcon::SMALL,'mimetype');
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
	    echo '<div id="WContentLocation">';
	    $retains = WImage::getRetainersFor($this->targetObject->Alias);
	    if(count($retains))
	    {
    	    printf('<dl><dt><label>%s</label></dt><dd><dl>', SLocalization::get('retained_by'));
    	    $i = 1;
    	    $currentClass = '';
    	    foreach($retains as $alias => $data)
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
    	            ,htmlentities($alias, ENT_QUOTES, 'utf-8')
    	        	,htmlentities($title, ENT_QUOTES, 'utf-8')
    	        	);
    	    }
    	    echo '</dl></dd></dl>';
	    }
	    else
	    {
	        printf('<h3>%s</h3>', SLocalization::get('item_has_not_been_retained'));
	    }
	    echo '</div>';
	}
}
?>