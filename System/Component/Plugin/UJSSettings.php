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
class UJSSettings
    extends BPlugin 
    implements 
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler,
        HWillSendHeadersEventHandler
{
    private static $location = array(
        '' => 'unused',
        'h' => 'header',
        //'f' => 'end_of_page'
    );
    
    private static function mkkey($k)
    {
        $k = basename($k, '.js');
        return preg_replace('/[^a-z0-9_]/mui','_', $k);
    }
    
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        $data = array();
        $conf = LConfiguration::get('UJSSettings');
        $allowedFiles = array();
        if(!empty($conf))
        {
            SErrorAndExceptionHandler::muteErrors();
            $allowedFiles = unserialize($conf);
            SErrorAndExceptionHandler::reportErrors();
        }
        $cssFiles = DFileSystem::FilesOf(SPath::SCRIPT,'/\.js$/i');
        $location = array();
        foreach (self::$location as $k => $v) 
        {
        	$location[SLocalization::get($v)] = $k;
        }
        foreach ($cssFiles as $file) 
        {
        	$key = self::mkkey($file);
        	$val = array_key_exists($file, $allowedFiles) ? $allowedFiles[$file] : '';
        	$data[$key] = array($val, AConfiguration::TYPE_SELECT, $location);
        }
        $e->addClassSettings($this, 'scripts', $data);
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        $files = DFileSystem::FilesOf(SPath::SCRIPT,'/\.js$/i');
        $cfg = array();
        foreach ($files as $file)
        {
            $key = self::mkkey($file);
            if(!empty($data[$key]) && array_key_exists($data[$key], self::$location))
            {
                //file => location
                $cfg[$file] = $data[$key];
            }
        }
        LConfiguration::set('UJSSettings', serialize($cfg));
    }
    
    public function HandleWillSendHeadersEvent(EWillSendHeadersEvent $e)
    {
        $conf = LConfiguration::get('UJSSettings');
        $allowedFiles = array();
        if(!empty($conf))
        {
            SErrorAndExceptionHandler::muteErrors();
            $allowedFiles = unserialize($conf);
            SErrorAndExceptionHandler::reportErrors();
        }
        $p = SPath::SCRIPT;
        $jsFiles = DFileSystem::FilesOf($p,'/\.js$/i');
        $h = $e->getHeader();
        foreach ($jsFiles as $file)
        {
            if(array_key_exists($file, $allowedFiles))
            {
                if($allowedFiles[$file] == 'h')
                {
                    $h->addScript('text/javascript', SPath::SCRIPT.$file);
                }
            }
        }
    }
}
?>