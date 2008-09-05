<?php
class BRequest extends BObject 
{
    abstract public static function get($key);
    
    abstract public static function alter($key, $value);
    
    abstract public static function has($key);
}
?>