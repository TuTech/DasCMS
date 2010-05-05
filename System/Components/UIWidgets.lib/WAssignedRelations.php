<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-06-11
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WAssignedRelations
    extends BWidget
    implements ISidebarWidget
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
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(WSidePanel::HELPER)
	        && $sidepanel->getTarget()->hasComposite('AssignedRelations')
	    );
	}

	public function getName()
	{
	    return 'assigned_relations';
	}

	public function getIcon()
	{
	    return new WIcon('attachment','',WIcon::SMALL,'mimetype');
	}

	public function processInputs()
	{
		$class = get_class($this);
		if(RSent::has($class.'_aliases')){
			$this->targetObject->setAssignedRelationsData(RSent::get($class.'_aliases',CHARSET));
		}
		if(RSent::has($class.'_formatter')){
			$this->targetObject->setAssignedRelationsFormatter(RSent::get($class.'_formatter',CHARSET));
		}
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
		$class = get_class($this);
	    printf('<div id="WAssignedRelations"><dl>'.
		   			'<dt><label for="%s_aliases">%s</label></dt>'.
		    		'<dd><textarea id="%s_aliases" name="%s_aliases">%s</textarea></dd>'.
		   			'<dt><label for="%s_formatter">%s</label></dt>'.
		    		'<dd><input id="%s_formatter" type="text" name="%s_formatter" value="%s" /></dd>'.
	    '</dl></div>'
			,$class
	    	,SLocalization::get('aliases_of_subcontents')
			,$class
			,$class
			,implode(', ', $this->targetObject->getAssignedRelationsData())

			,$class
	    	,SLocalization::get('formatter_for_contents')
			,$class
			,$class
			,$this->targetObject->getAssignedRelationsFormatter()
		);
	}

	public function associatedJSObject()
	{
	    return null;//'org.bambuscms.weventplanner';
	}
}
?>