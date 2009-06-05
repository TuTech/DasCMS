<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-05
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Request
 */
class RFiles extends BRequest 
{
    //read data from server path
    //merge data with $_POST

    private static $data = null;
    
    private function __construct(){}
    
    private static function init()
    {
        global $_FILES;
        if(self::$data == null)
        {
            self::$data = $_FILES;
        }
    }
    
    public static function getTempName($key)
    {
        self::init();
        $ret = '';
        if(array_key_exists($key, self::$data))
        {
            $ret = self::$data[$key]['tmp_name'];
        }
        return $ret;
    }
    
    public static function getName($key)
    {
        self::init();
        $ret = '';
        if(array_key_exists($key, self::$data))
        {
            $ret = self::$data[$key]['name'];
        }
        return $ret;
    }
    
    public static function setName($key, $newName)
    {
        self::init();
        $ret = '';
        if(array_key_exists($key, self::$data))
        {
            self::$data[$key]['name'] = $newName;
        }
        return $ret;
    }
    
    public static function getType($key)
    {
        self::init();
        $ret = '';
        if(array_key_exists($key, self::$data))
        {
            $ret = self::$data[$key]['type'];
        }
        return $ret;
    }
    
    public static function getSize($key)
    {
        self::init();
        $ret = '';
        if(array_key_exists($key, self::$data))
        {
            $ret = self::$data[$key]['size'];
        }
        return $ret;
    }
    
    public static function has($key)
    {
        self::init();
        return array_key_exists($key, self::$data);
    }
    
    public static function hasFile($key)
    {
        self::init();
        return self::has($key) && is_uploaded_file(self::getTempName($key));
    }
    
    public static function move($key, $to)
    {
        self::init();
        return move_uploaded_file(self::getTempName($key), $to);
    }
}
?>