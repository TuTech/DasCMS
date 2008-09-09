<?php
class RSent extends BRequest 
{
    //read data from server path
    //merge data with $_POST

    private static $data = null;
    
    private function __construct(){}
    
    private static function init()
    {
        global $_POST;
        if(self::$data == null)
        {
            self::$data = $_POST;
            if(get_magic_quotes_gpc())
            {
                foreach ($_POST as $key => $value) 
                {
                    self::$data[$key] = stripslashes($value);
                }
            }
        }
    }
    
    public static function get($key)
    {
        self::init();
        $ret = '';
        if(array_key_exists($key, self::$data))
        {
            $ret = self::$data[$key];
        }
        return $ret;
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
    
    public static function alter($key, $value)
    {
        self::init();
        self::$data[$key] = $value;
    }
    
    public static function data()
    {
        return self::$data;
    }
}
?>