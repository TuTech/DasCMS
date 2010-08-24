<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-06-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class UContentLookup
    extends BPlugin 
    implements 
        IAjaxAPI,
        IGlobalUniqueId
{
    const GUID = 'org.bambuscms.plugin.contentlookup';
    const CLASS_NAME = 'UContentLookup';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function isAjaxCallableFunction($function, array $parameterNames)
    {
        return $function == 'provideContentLookup'
            || $function == 'provideImageContentLookup'; 
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
		$itemsPerPage = 15;

		$res = Core::Database()
			->createQueryForClass($this)
			->call('list'.ucfirst($opt))
			->withParameters('%'.$filter.'%', $itemsPerPage+1, $page * $itemsPerPage - $itemsPerPage);
		while($erg = $res->fetchResult())
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
	
	public static function provideImageContentLookup(array $namedParameters)
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
	    $res = Core::Database()
			->createQueryForClass($this)
			->call('listImg'.ucfirst($opt))
			->withParameters('%'.$filter.'%', $itemsPerPage+1, $page * $itemsPerPage - $itemsPerPage);
		while($erg = $res->fetchResult())
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
}
?>