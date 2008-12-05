<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-30
 * @license GNU General Public License 3
 */
abstract class BFeed extends BObject
{
	const TITLE = 1;
	const LINK = 2;
	const DESCRIPTION = 3;
	const LANGUAGE = 4;
	const COPYRIGHT = 5;
	const WEBMASTER = 6;
	const PUB_DATE = 7;
	const LAST_BUILD_DATE = 8;
	const GENERATOR = 9;
	const TTL = 10;
	const RATING = 11;
	const IMAGE = 12;
	
	const AUTHOR = 13;
	const ENCLOSURE = 14;
	const GUID = 15;
	const SOURCE = 16;
	
	const URL = 17;
	const WIDTH = 18;
	const HEIGHT = 19;
	const TYPE = 20;
	const LENGTH = 21;
	const CATEGORY = 22;
		
	abstract function __construct(BContent $datasource);
	
	abstract function __toString();
	
	public static function getURLForFeed($alias)
	{
	    return sprintf('%sAtom.php/%s', SLink::base(), $alias);
	}
}
?>