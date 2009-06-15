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
class WImageContentLookup extends WContentLookup implements ISidebarWidget
{
    const CLASS_NAME = 'WImageContentLookup';
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
		    'now' => time(),
		    'continueList' => false
		);
		$opts = array('priv', 'pub', 'sched', 'all');
		$opt = (isset($namedParameters['mode']) && in_array($namedParameters['mode'], $opts)) ? $namedParameters['mode'] : 'all';
		$filter = isset($namedParameters['filter']) ?  $namedParameters['filter'] : '';
		$page = isset($namedParameters['page']) ?  max(1, intval($namedParameters['page'])) : 1;
		$map['continueList'] = $page > 1;
		$lastMan = null;
		$itemsPerPage = 10;
	    $res = QWContentLookup::fetchContentList($opt, $filter, $page, $itemsPerPage, WImage::getSupportedMimeTypes());
		while($erg = $res->fetch())
		{
			list($ctype, $alias, $ttl, $pub) = $erg;
			$pub = ($pub == '0000-00-00 00:00:00') ? 0 : strtotime($pub);
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
	    return 'image_lookup';
	}
		
	public function getIcon()
	{
	    return new WIcon('image','',WIcon::SMALL,'mimetype');
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
	    $select = '<select id="WImageContentLookupMode">';
	    foreach ($opts as $val => $ttl)
	    {
	        $select .= sprintf('<option value="%s">%s</option>',$val, SLocalization::get($ttl));
	    }
	    $select .= '</select>';
	    $Items->add(
	        sprintf("<label>%s</label>", SLocalization::get('search_contens')),
	        '<div id="WICLSearchBox">'.
		            '<input type="text" autocomplete="off" id="WImageContentLookupFilter" onchange="org.bambuscms.wcontentlookup.filter();" '.
		            'onkeyup="org.bambuscms.wcontentlookup.filter();" />'.$select.
		            '</div>'."\n"
	    );
		$html = '<div id="WImageContentLookup"></div>';
		return strval($Items).$html;
	}
	
	public function associatedJSObject()
	{
	    return 'org.bambuscms.wimagecontentlookup';
	}
}
?>