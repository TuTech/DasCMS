<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-13
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Request
 */
class RServer extends BRequest 
{
    private static $data = null;
    
    private function __construct(){}
    
    private static function init()
    {
        global $_SERVER;
        if(self::$data == null)
        {
            self::$data = $_SERVER;
			if(!isset(self::$data["REMOTE_ADDR"])){
				self::$data["REMOTE_ADDR"] = '0.0.0.0';
			}
        }
    }
    
    public static function get($key, $encoding = "ISO-8859-15")
    {
        self::init();
        $ret = '';
        if(array_key_exists($key, self::$data))
        {
            $ret = self::$data[$key];
        }
        if($encoding == "ISO-8859-15")
        {
            return $ret;
        }
        return mb_convert_encoding($ret, $encoding, 'ISO-8859-15');
    }
    
    public static function has($key)
    {
        self::init();
        return array_key_exists($key, self::$data);
    }
    
    public static function hasValue($key)
    {
        self::init();
        return (array_key_exists($key, self::$data) && !empty(self::$data[$key]));
    }
    
    public static function alter($key, $value, $encoding = "ISO-8859-15")
    {
        self::init();
        self::$data[$key] = mb_convert_encoding($value, 'ISO-8859-15', $encoding);
    }
    
    public static function data($encoding = "ISO-8859-15")
    {
        self::init();
        $data = self::$data;
        if(strtoupper($encoding) != 'ISO-8859-15')
        {
            $data = array();
            foreach (self::$data as $k => $v) 
            {
                $data[mb_convert_encoding($k, $encoding, 'ISO-8859-15')] = mb_convert_encoding($v, $encoding, 'ISO-8859-15');
            }
            
        }
        return self::$data;
    }
    
    /**
     * @return integer
     */
    public static function getNumericRemoteAddress()
    {
        $ra = self::getRemoteAddress();
        list($a, $b, $c, $d) = explode('.', $ra);
        return hexdec(sprintf('%02x%02x%02x%02x', $a, $b, $c, $d));
    }

	public static function getRemoteAddress()
    {
        self::init();
		$ra = isset(self::$data["REMOTE_ADDR"]) ? strtolower(self::$data["REMOTE_ADDR"]) : '0.0.0.0';
        return $ra == 'localhost' ? '127.0.0.1' : $ra;
    }


}
?>