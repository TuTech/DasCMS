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
    implements 
        IShareable
{
    const TYPE_TEXT = 1;
    const TYPE_CHECKBOX = 2;
    const TYPE_SELECT = 3;
    const TYPE_PASSWORD = 4;
	
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
            $prevfile = SPath::CONTENT.'configuration/system.prev.php';
            $file = SPath::CONTENT.'configuration/system.php';
            if(RURL::has('@previousconfig') && file_exists($prevfile) && PAuthorisation::has('org.bambuscms.login'))
            {
                //show with previous version
                $file = $prevfile;
            }
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
            try{
                chdir(BAMBUS_CMS_ROOTDIR);
                SErrorAndExceptionHandler::muteErrors();
                //make a backup
                $oc = DFileSystem::Load(SPath::CONTENT.'configuration/system.php');
                DFileSystem::Save(SPath::CONTENT.'configuration/system.prev.php', $oc);
                
                //save new config
                $file = SPath::CONTENT.'configuration/system.php';
                DFileSystem::SaveData($file, self::$configuration);
                SNotificationCenter::report('message', 'configuration_saved');
                self::$configurationChanged = false;
                
                SErrorAndExceptionHandler::reportErrors();
            }
            catch(Exception $e)
            {
                SNotificationCenter::report('warning', 'configuration_not_saved');
            }
        }
    }

}
?>
