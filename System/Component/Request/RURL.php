<?php
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
                    $key = urldecode(array_shift($requests));
                    $value = urldecode(array_shift($requests));
                    self::$data[$key] = $value;
                }
            }
            //more data sent by $_GET
            $evilMagic = get_magic_quotes_gpc();
            foreach ($_GET as $key => $value) 
            {
            	self::$data[$key] = ($evilMagic ? stripslashes($value) : $value);
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
        self::init();
        return self::$data;
    }
}
?>