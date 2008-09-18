<?php
class SLocalization extends BSystem 
{
	/**
	 * do not instanciate
	 */
	private function __construct()
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
        $language = 'de_DE';
        $file = 'System/Resource/Translation/'.$language.'.strings';
        if(!file_exists($file))
        {
        	$language = 'nu_LL';
        }
        return $language;
    }
    
    /**
     * load language
     */
    private static function loadLanguage()
    {
        self::$currentLang = self::determineLanguage();
        $file = 'System/Resource/Translation/'.self::$currentLang.'.strings';
        mb_internal_encoding("UTF-8");
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
        	self::$currentLang = 'NU_LL';
        }
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