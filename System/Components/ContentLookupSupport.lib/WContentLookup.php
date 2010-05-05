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
class WContentLookup 
    extends BWidget 
    implements 
        ISidebarWidget
{
    const CLASS_NAME = 'WContentLookup';
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
		return $sidepanel->isMode(WSidePanel::CONTENT_LOOKUP);
	}
	
	public function getName()
	{
	    return 'content_lookup';
	}
		
	public function getIcon()
	{
	    return new WIcon('link','',WIcon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
	}
	
	public function __toString()
	{
	    $Items = new WNamedList();
	    $Items->setTitleTranslation(false);
	    $opts = array('pub' => 'published', 'sched' => 'scheduled_publication', 'all' => 'all', 'priv' => 'not_published');
	    $select = '<select id="WContentLookupMode">';
	    foreach ($opts as $val => $ttl)
	    {
	        $select .= sprintf('<option value="%s">%s</option>',$val, SLocalization::get($ttl));
	    }
	    $select .= '</select>';
	    $Items->add(
	        sprintf("<label>%s</label>", SLocalization::get('search_contens')),
	        '<div id="WCLSearchBox">'.
		            '<input type="text" autocomplete="off" id="WContentLookupFilter" />'.
	                $select.
		            '</div>'."\n"
	    );
		$html = '<div id="WContentLookup"></div>';
		return strval($Items).$html;
	}
	
	public function associatedJSObject()
	{
	    return 'org.bambuscms.wcontentlookup';
	}
}
?>