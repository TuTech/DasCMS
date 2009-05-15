<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-15
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Plugin
 */
class UCSSSettings
    extends BPlugin 
    implements 
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler,
        HWillSendHeadersEventHandler
{
    private static $media = array(
        '' => 'unused',
    	'a' => 'all',
        'b' => 'braille',
        'e' => 'embossed',
        'h' => 'handheld',
        'p' => 'print',
        'j' => 'projection',
        's' => 'screen',
        'c' => 'speech',
        'y' => 'tty',
        'v' => 'tv'
    );
    
    private static function mkkey($k)
    {
        $k = basename($k, '.css');
        return preg_replace('/[^a-z0-9_]/mui','_', $k);
    }
    
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        $data = array();
        $conf = LConfiguration::get('UCSSSettings');
        $cssAllowedFiles = array();
        if(!empty($conf))
        {
            SErrorAndExceptionHandler::muteErrors();
            $cssAllowedFiles = unserialize($conf);
            SErrorAndExceptionHandler::reportErrors();
        }
        
        $cssFiles = DFileSystem::FilesOf(SPath::DESIGN,'/\.css$/i');
        $media = array();
        foreach (self::$media as $k => $v) 
        {
        	$media[SLocalization::get($v)] = $k;
        }
        foreach ($cssFiles as $file) 
        {
        	$key = self::mkkey($file);
        	$val = array_key_exists($file, $cssAllowedFiles) ? $cssAllowedFiles[$file] : '';
        	$data[$key] = array($val, AConfiguration::TYPE_SELECT, $media);
        }
        $e->addClassSettings($this, 'stylesheets', $data);
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        $cssFiles = DFileSystem::FilesOf(SPath::DESIGN,'/\.css$/i');
        $cfg = array();
        foreach ($cssFiles as $file)
        {
            $key = self::mkkey($file);
            if(!empty($data[$key]) && array_key_exists($data[$key], self::$media))
            {
                //file => media
                $cfg[$file] = $data[$key];
            }
        }
        LConfiguration::set('UCSSSettings', serialize($cfg));
    }
    
    public function HandleWillSendHeadersEvent(EWillSendHeadersEvent $e)
    {
        $conf = LConfiguration::get('UCSSSettings');
        $cssAllowedFiles = array('default.css' => 'a');
        if(!empty($conf))
        {
            SErrorAndExceptionHandler::muteErrors();
            $cssAllowedFiles = unserialize($conf);
            SErrorAndExceptionHandler::reportErrors();
        }
        $p = SPath::DESIGN;
        $cssFiles = DFileSystem::FilesOf($p,'/\.css$/i');
        $h = $e->getHeader();
        foreach ($cssFiles as $file)
        {
            if(array_key_exists($file, $cssAllowedFiles))
            {
                $h->addLink('utf-8', sprintf('css.php?v=%d&f=%s', filemtime($p.$file), $file),null,'text/css', 
                            null, 'stylesheet', null, self::$media[$cssAllowedFiles[$file]]);
                //<link rel=\"stylesheet\" href=\"./css.php?v=%d&amp;f=default.css\" type=\"text/css\" />
            }
        }
    }
}
?>