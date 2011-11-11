<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-05
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Request
 */
class RURL extends BRequest
{
    //read data from server path
    //merge data with $_GET

    private static $data = null;

    private function __construct(){}

    private static function init()
    {
        global $_SERVER, $_GET;
        if(self::$data == null)
        {
            self::$data = array();
            //data sent by path
            if(!empty($_SERVER['PATH_INFO']))
            {
                //remove / from beginning
                $path = substr($_SERVER['PATH_INFO'],1);

                //split to key/value pairs
                $requests = explode('/', $path);
                while(count($requests) >= 2)
                {
                    $key = array_shift($requests);
                    $value = array_shift($requests);
                    self::$data[$key] = $value;
                }
            }
            //more data sent by $_GET
            foreach ($_GET as $key => $value)
            {
            	self::$data[$key] = $value;
            }
            self::$data = BRequest::cleanGPC(self::$data);
        }
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

    public static function data($encoding = "ISO-8859-15")
    {
        self::init();
        $data = self::$data;
        if(strtoupper($encoding) != CHARSET)
        {
            $data = BRequest::recodeCharset($data, $encoding);
        }
        return $data;
    }

    public static function get($key, $encoding = "ISO-8859-15")
    {
        self::init();
                $ret = '';
        if(array_key_exists($key, self::$data))
        {
            $ret = self::$data[$key];
        }
        return BRequest::recodeCharset($ret, $encoding);
    }

    public static function alter($key, $value, $encoding = "ISO-8859-15")
    {
        self::init();
        self::$data[$key] = mb_convert_encoding($value, CHARSET, $encoding);
    }
}
?>