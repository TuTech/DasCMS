<?php
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