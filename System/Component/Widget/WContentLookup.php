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
class WContentLookup extends BWidget implements ISidebarWidget
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
	
	public static function provideContentLookup(array $namedParameters)
	{
		$map = array(
		    'type' => array(),
		    'items' => array(),
		    'hasMore' => false,
		    'now' => time()
		);
		$opts = array('priv', 'pub', 'sched', 'all');
		$opt = (isset($namedParameters['mode']) && in_array($namedParameters['mode'], $opts)) ? $namedParameters['mode'] : 'all';
		$filter = isset($namedParameters['filter']) ?  $namedParameters['filter'] : '';
		$page = isset($namedParameters['page']) ?  max(1, intval($namedParameters['page'])) : 1;
		$lastMan = null;
		$itemsPerPage = 20;
		$hasMore = 
	    $res = QWContentLookup::fetchContentList($opt, $filter, $page, $itemsPerPage);
		while($erg = $res->fetch())
		{
			list($ctype, $alias, $ttl, $pub) = $erg;
			$pub = strtotime($pub);
			if($ctype != $lastMan)
			{
			    $manID = count($map['type']);
			    $map['type'][$manID] = $ctype;
				$lastMan = $ctype;
			}
			if(count($map['items']) < $itemsPerPage)
			{
			    $map['items'][] = array($alias, $ttl, $pub, $manID);
			}
			else
			{
			    $map['hasMore'] = true;
			}
		}
		$res->free();
		return $map;
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
		            '<input type="text" autocomplete="off" id="WContentLookupFilter" onchange="org.bambuscms.wcontentlookup.filter();" '.
		            'onkeyup="org.bambuscms.wcontentlookup.filter();" />'.$select.
		            '</div>'."\n"
	    );
		$html = '<div id="WContentLookup"></div>';
		return strval($Items).$html;
	}
}
?>