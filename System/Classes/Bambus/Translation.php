<?php 
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 12.06.2006
 * @license GNU General Public License 3
 */
class Translation extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'Translation';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
			$this->FileSystem = FileSystem::alloc();
			$this->FileSystem->init();
			
			if(!defined('BAMBUS_CMS_DEFAULT_LANGUAGE'))
			{
				define('BAMBUS_CMS_DEFAULT_LANGUAGE', 'de');
			}
			
			if(defined('BAMBUS_ACCESS_TYPE') && constant('BAMBUS_ACCESS_TYPE') == 'management')
	    	{
	    		$this->loadTranslation(BAMBUS_CMS_DEFAULT_LANGUAGE);
	    	}
    	}
    }
	//end IShareable
	
    var $translations = array();
    var $failedTranslations = array();

    function __construct()
    {
        parent::Bambus();
    }

	public function __get($var)
	{
		return $this->sayThis($var);
	}

    function tprint($key)
    {
    	echo $this->sayThis($key);
    }
 
    function treturn($key)
    {
    	return $this->sayThis($key);
    }
    
    function getAllLanguages($asOption = false)
    {
    	$languages = array('de' => 'Deutsch');
    	$translationIndex = $this->FileSystem->read($this->pathToFile('translationIndex'));
    	$lines = explode("\n", $translationIndex);
    	foreach($lines as $line)
    	{
    		$line = trim($line);
    		if(strpos($line, "\t") !== false)
    		{
    			$parts = explode("\t", $line);
    			$langKey = array_shift($parts);
    			$landDesc = implode(" ", $parts);
    			$languages[$langKey] = $landDesc;
    		}
    	}
    	ksort($languages);
    	if($asOption)
    	{
    		$html = array();
    		$selected = (isset($this->post['language'])) ? $this->post['language'] : false;
    		foreach($languages as $key => $desc)
    		{
    			$sel = ($key == $selected) ? ' selected="selected"' : '';
    			$html[] = sprintf('<option value="%s"%s>%s</option>'."\n", $key, $sel, htmlentities($desc));
      		}
      		$languages = $html;
    	}
    	return $languages;
    }
    
    function sayThis($sentence,$in = '')
    {
    	$ret = '';
        $translations = &$this->translations;
		$in = empty($in) ? BAMBUS_CMS_DEFAULT_LANGUAGE : $in;

		if(empty($translations[$in]))
			$this->loadTranslation($in);
       
        if(!isset($translations[$in][$sentence]))
        {
        	$this->failedTranslations[$sentence] = $sentence;
        	$ret = ucfirst(str_replace('_', ' ', $sentence));
        }
        else
			$ret = $translations[$in][$sentence];
        return $ret;
    }
    
    function loadTranslation($language)
    {
	    $translations = &$this->translations;
        $translations[$language] = array();
        if(file_exists(parent::pathTo('translation').$language.'.translation'))
        {
            $data = $this->FileSystem->read(parent::pathTo('translation').$language.'.translation');
            $lines = explode("\n", $data);
            foreach($lines as $line)
            {
                $pair = explode("\t",$line);
                $key = $pair[0];
                unset($pair[0]);
                $translations[$language][$key] = implode(" ",$pair);
            }
        }
    }
    
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
    function loadApplicationTranslation($language = '')
    {
    	$language = (empty($language)) ? BAMBUS_CMS_DEFAULT_LANGUAGE : $language;
    	$this->loadTranslation($language);
        $translations = &$this->translations;
        if(file_exists(BAMBUS_APPLICATION_DIRECTORY.'/Resource/Translation/'.$language.'.translation'))
        {
            $data = $this->FileSystem->read(BAMBUS_APPLICATION_DIRECTORY.'/Resource/Translation/'.$language.'.translation');
            $lines = explode("\n", $data);
            foreach($lines as $line)
            {
                $pair = explode("\t",$line);
                $key = $pair[0];
                unset($pair[0]);
                $translations[$language][$key] = implode(" ",$pair);
            }
        }
    }
}
?>