<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-08-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SLocalization 
    extends 
        BObject
    implements
        IGlobalUniqueId,
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler
{
    const GUID = 'org.bambuscms.system.localization';
    
    private static $confKeys = array(
        'locale' => 'locale',
        'timezone' => 'timezone',
        'date_format' => 'dateformat'
    );
    
    public function getClassGUID()
    {
        return self::GUID;
    }

    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        $tz = array();
        $loc = array();
        $fp = fopen(SPath::SYSTEM_RESOURCES.'timezones.txt', 'r');
        while($row = fgets($fp,255))
        {
            $tz[] = trim($row);
        }
        fclose($fp);

		$loc = Core::dataFromJSONFile(SPath::SYSTEM_RESOURCES.'locale.json');
		$loc = array_flip($loc);
		
        //locale, timezone, dateformat
        $e->addClassSettings($this, 'localization', array(
        	'locale' => array(Core::settings()->get('locale'), Settings::TYPE_SELECT, $loc, 'locale'),
        	'timezone' => array(Core::settings()->get('timezone'), Settings::TYPE_SELECT, $tz, 'timezone'),
        	'date_format' => array(Core::settings()->get('dateformat'), Settings::TYPE_TEXT, null, 'date_format')
        ));
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        foreach (self::$confKeys as $mk => $cc)
        {
            if(isset($data[$mk]))
            {
                Core::settings()->set($cc, $data[$mk]);
            }
        }
    }
    
    public function __construct()
	{
	}
	
	/**
	 * @var string 
	 */
	private static $currentLang = null;
	/**
	 * @var array
	 */
	private static $translations = null;
	
	/**
	 * find best language for current client 
	 */
    private static function determineLanguage()
    {
    	// add language given in url
        // add browser preferences
        // add de_DE
        // use earliest given available in trans dir
		$langs = array(
			Core::settings()->get('locale'),
			'de-DE'
		);
		foreach ($langs as $lang){
			if(file_exists('System/ClientData/org/bambuscms/localization/'.$lang.'.json')){
				return $lang;
			}
		}
        return 'de-DE';
    }

	public static function getCurrentLanguageCode(){
		return self::determineLanguage();
	}

    /**
     * load language
     */
    private static function loadLanguage()
    {
        self::$currentLang = self::determineLanguage();
        $file = 'System/Resource/Translation/'.self::$currentLang.'.strings';
        mb_internal_encoding(CHARSET);
        self::$translations = array();
        if(file_exists($file))
        {
            $data = file($file);
            foreach ($data as $line) 
            {
            	$tab = mb_strpos($line, "\t");
            	if($tab > 0 && mb_strlen($line) > ($tab+2))
            	{
	            	$key = mb_substr($line, 0, $tab);
	            	$value = mb_substr($line, $tab+1, -1);
	            	self::$translations[$key] = $value;
            	}
            }
        }
        else
        {
        	self::$currentLang = 'nu_LL';
        }
    }

	public static function all()
	{
		if(self::$translations === null)
		{
			self::loadLanguage();
		}
		return self::$translations;
	}
    
    /**
     * return translation 
     * @param string $key
     * @param array $subjects
     */
	public static function get($key, array $subjects = array())
	{
		if(self::$translations === null)
		{
			self::loadLanguage();
		}
        if(count($subjects) > 10)
        {
            throw new XArgumentException('too many subjects for a translation',10);
        }
		if(array_key_exists($key, self::$translations))
		{
			$message = self::$translations[$key];
			for ($i = 0; $i < count($subjects); $i++)
			{
				$message = str_replace('@'.$i, $subjects[$i], $message);
			}
			return $message;
		}
		else
		{
			return str_replace('_', ' ',$key);
		}
	}
	
    /**
     * echo translation 
     * @param string $key
     * @param array $subjects
     */
	public static function out($key, array $subjects = array())
	{
		echo self::get($key, $subjects);
	}
	
}
?>