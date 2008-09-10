<?php
class RSession extends BRequest 
{
    private static $session = null;
    
    public static function start()
    {
        global $_SESSION;
        if(self::$session == null)
        {
            session_start();
            self::$session = &$_SESSION;
        }
    }
    
    private static function init()
    {
        if(self::$session == null)
        {
            throw new XUndefinedException('no session');
        }
    }

    public static function destroy()
    {
        session_destroy();
        self::$session = null;
    }
    
    public static function get($key)
    {
        self::init();
        $ret = '';
        if(array_key_exists($key, self::$session))
        {
            $ret = self::$session[$key];
        }
        return $ret;
    }
    
    public static function has($key)
    {
        self::init();
        return array_key_exists($key, self::$session);
    }
    
    public static function hasValue($key)
    {
        self::init();
        return (array_key_exists($key, self::$session) && !empty(self::$session[$key]));
    }
    
    public static function set($key, $value)
    {
        self::init();
        self::$session[$key] = $value;
    }
    
    public static function data()
    {
        self::init();
        return self::$session;
    }
}
?>