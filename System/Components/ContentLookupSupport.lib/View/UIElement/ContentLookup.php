<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-29
 * @license GNU General Public License 3
 * @deprecated
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_ContentLookup 
    extends _View_UIElement 
    implements 
        ISidebarWidget
{
    const CLASS_NAME = 'View_UIElement_ContentLookup';
	/**
	 * get an array of string of all supported classes 
	 * if it supports object, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(View_UIElement_SidePanel $sidepanel)
	{
		return $sidepanel->isMode(View_UIElement_SidePanel::CONTENT_LOOKUP);
	}
	
	public function getName()
	{
	    return 'content_lookup';
	}
		
	public function getIcon()
	{
	    return new View_UIElement_Icon('link','',View_UIElement_Icon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(View_UIElement_SidePanel $sidepanel)
	{
	}
	
	public function __toString()
	{
	    $Items = new View_UIElement_NamedList();
	    $Items->setTitleTranslation(false);
	    $opts = array('pub' => 'published', 'sched' => 'scheduled_publication', 'all' => 'all', 'priv' => 'not_published');
	    $select = '<select id="View_UIElement_ContentLookupMode">';
	    foreach ($opts as $val => $ttl)
	    {
	        $select .= sprintf('<option value="%s">%s</option>',$val, SLocalization::get($ttl));
	    }
	    $select .= '</select>';
	    $Items->add(
	        sprintf("<label>%s</label>", SLocalization::get('search_contens')),
	        '<div id="WCLSearchBox">'.
		            '<input type="text" autocomplete="off" id="View_UIElement_ContentLookupFilter" />'.
	                $select.
		            '</div>'."\n"
	    );
		$html = '<div id="View_UIElement_ContentLookup"></div>';
		return strval($Items).$html;
	}
	
	public function associatedJSObject()
	{
	    return 'org.bambuscms.wcontentlookup';
	}
}
?>