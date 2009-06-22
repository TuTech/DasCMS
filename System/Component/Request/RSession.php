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
class RSession extends BRequest 
{
    private static $session = null;
    
    public static function start()
    {
        if(self::$session === null)
        {
            session_start();
            global $_SESSION;
            self::$session = &$_SESSION;
            if(self::$session === null)
            {
                self::$session = array();
            }
        }
    }
    
    private static function init()
    {
        if(self::$session === null)
        {
            throw new XUndefinedException('no session');
        }
    }

    public static function destroy()
    {
        self::start();
        session_destroy();
        self::$session = array();
    }
    
    public static function reset()
    {
        session_regenerate_id(true);
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
        global $_SESSION;
        self::$session[$key] = $value;
        $_SESSION[$key] = $value;
    }
    
    public static function data()
    {
        self::init();
        return self::$session;
    }
}
?>