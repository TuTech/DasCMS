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
class View_UIElement_AssignedRelations
    extends _View_UIElement
    implements ISidebarWidget
{
	private $targetObject = null;
	private static $retains = array();
	/**
	 * get an array of string of all supported classes
	 * if it supports object, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(View_UIElement_SidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(View_UIElement_SidePanel::HELPER)
	        && $sidepanel->getTarget()->hasComposite('AssignedRelations')
	    );
	}

	public function getName()
	{
	    return 'assigned_relations';
	}

	public function getIcon()
	{
	    return new View_UIElement_Icon('attachment','',View_UIElement_Icon::SMALL,'mimetype');
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
		$currentFormatter = $this->targetObject->getAssignedRelationsFormatter();
		$formatters = Formatter_Container::getFormatterList();
		$options = array(' - '.SLocalization::get('none').' - '  => '');
		foreach ($formatters as $f){
			$options[$f] = $f;
		}
		$selectHTML = "";
		foreach ($options as $title => $value){
			$selectHTML .= sprintf(
					'<option value="%s"%s>%s</option>',
					htmlentities($value, ENT_QUOTES, CHARSET),
					$value == $currentFormatter ? ' selected="selected"' : '',
					htmlentities($title, ENT_QUOTES, CHARSET)
					);
		}
		$class = get_class($this);

	    printf('<div id="View_UIElement_AssignedRelations"><dl>'.
		   			'<dt><label for="%s_aliases">%s</label></dt>'.
		    		'<dd><textarea id="%s_aliases" name="%s_aliases">%s</textarea></dd>'.
		   			'<dt><label for="%s_formatter">%s</label></dt>'.
		    		'<dd><select id="%s_formatter" name="%s_formatter">%s</select></dd>'.
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
			,$selectHTML
		);
	}

	public function associatedJSObject()
	{
	    return null;//'org.bambuscms.weventplanner';
	}
}
?>