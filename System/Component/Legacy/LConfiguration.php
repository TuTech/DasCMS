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
    extends BLegacy
    implements HWillSendHeadersEventHandler 
{
    private static $configuration = null;
    private static $configurationChanged = false;
    private static $instanceForSavingOnEnd = null;
    
    const CLASS_NAME = 'LConfiguration';

    /**
     * @return LConfiguration
     */
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
    
    public function HandleWillSendHeadersEvent(EWillSendHeadersEvent $e)
    {
        self::init();
        $confMeta = array(
            'google_verify_header' => 'verify-v1',
            'copyright' => 'copyright',
            'publisher' => 'DC.publisher',
            'generator' => BAMBUS_VERSION
        );
        foreach($confMeta as $key => $metaKey)
        {
            if(!empty(self::$configuration[$key]))
            {
                $e->getHeader()->addMeta(self::$configuration[$key],$metaKey);
            }
        }
    }
}
?>
