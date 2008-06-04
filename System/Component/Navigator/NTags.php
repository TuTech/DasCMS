<?php
/**
 * @package Bambus
 * @subpackage Navigators
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.04.2008
 * @license GNU General Public License 3
 * @todo implement
 */
class NTags
{
	//show tag cloud
	//scale 5,10,25,50,100
	//max is usage of most used tag
	//min is usage of min used tag
	
	//scale 5, min = 2, max = 10
	// --> 0 - 8
	//100/8 = factor = 12,5
	
	// eg 5:
	// (5-min) * factor = 37,5%
	
	//scale mapped to percentages:
	//  5: 20%
	// 10: 10%
	// 25:  4%
	// 50:  2%
	//100:  1%
	
	
}
?>