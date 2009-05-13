<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2006-09-22
 * @license GNU General Public License 3
 * @deprecated
 */
/**
 * @package Bambus
 * @subpackage Legacy
 */
class LConfiguration 
    extends 
        BLegacy
    implements 
        IShareable
{
    private static $configuration = null;
    private static $configurationChanged = false;
    private static $instanceForSavingOnEnd = null;
    
    const CLASS_NAME = 'LConfiguration';

    /**
     * @return LConfiguration
     */
    public static function getSharedInstance()
    {
        self::init();
        return self::$instanceForSavingOnEnd;
    }
    
    private static function init()
    {
        if(self::$configuration == null)
        {
            $file = SPath::CONTENT.'configuration/system.php';
            self::$configuration = DFileSystem::LoadData($file);
            self::$instanceForSavingOnEnd = new LConfiguration();
        }
    }
    
    /**
     * export config to array
     *
     * @return array
     */
    public static function as_array()
    {
        self::init();
        return self::$configuration;
    }
    
    /**
     * get config value
     *
     * @return string
     */
    public static function get($var)
    {
        self::init();
        return strval((isset(self::$configuration[$var])) ? self::$configuration[$var] : '');
    }
    
    /**
     * get config value or the given default value
     *
     * @return string
     */
    public static function getOrDefault($var, $default)
    {
        $val = self::get($var);
        if($val == '')
        {
            $val = $default;
        }
        return $val;
    }
    
    
    
    /**
     * set config value
     *
     * @param string $var
     * @param string $value
     */
    public static function set($var, $value)
    {
        self::init();
        if(self::get($var) != $value)
        {
            self::$configuration[$var] = strval($value);
            self::$configurationChanged = true;
        }
    }
    
    /**
     * check if config has key
     *
     * @param string $var
     * @return bool
     */
    public static function exists($var)
    {
        self::init();
        return isset(self::$configuration[$var]);
    }
    
    /**
     * save when script is finished
     */
    public function __destruct()
    {
        if(self::$configurationChanged)
        {
            SNotificationCenter::report('message', 'configuration_saved');
            $file = SPath::CONTENT.'configuration/system.php';
            DFileSystem::SaveData($file, self::$configuration);
            self::$configurationChanged = false;
        }
    }

}
?>
