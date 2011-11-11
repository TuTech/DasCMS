<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-05
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
 */
abstract class BRequest 
{
    public static function get($key){}

    public static function alter($key, $value){}

    public static function has($key){}

    protected static function cleanGPC($data){
    	if(!get_magic_quotes_gpc()){
    		return $data;
    	}
		if(is_array($data)){
			foreach($data as $k => $v){
				$data[$k] = BRequest::cleanGPC($v);
			}
		}
		else{
			$data = stripslashes($data);
		}
		return $data;
    }

    protected static function recodeCharset($data, $charset){
		if(is_array($data)){
			$newData = array();
			foreach($data as $k => $v){
				$newData[mb_convert_encoding($k, $charset, CHARSET)] = BRequest::recodeCharset($v, $charset);
			}
		}
		else{
			$newData = mb_convert_encoding($data, $charset, CHARSET);
		}
		return $newData;
    }
}
?>