<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-05-06
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SErrorAndExceptionHandler
    extends 
        BSystem 
    implements
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler
{
    private static $error = null;
    private static $errorMessage = null;
    
    private static $report = true;
    private static $reportSkipOnce = false;
    
    private static $showInfoMessage = false;
    private static $hideErrors = false;
    private static $err_html = 
        '<div style="font-family:sans-serif;border:1px solid #a40000;">
            <div style="border:1px solid #cc0000;z-index:1000000;padding:10px;background:#a40000;color:white;">
                <h1 style="border-bottom:1px solid #cc0000;font-size:16px;">%s <code>%d</code> in "%s" at line %d</h1>
                <p>%s</p>
                <p><pre>%s</pre></p>
    			<p>CWD: %s</p>
            </div>
        </div>';
    
    private static $err_mail = 
    	"\r\n\r\n%s (%d) in \"%s\" at line %d\n\n%s\n\n%s\n\ncwd: %s\nscript: %s\nurl: %s";
    
    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
        $data = array(
        	'mail_webmaster_on_error' => array(LConfiguration::get('mail_webmaster_on_error'), AConfiguration::TYPE_CHECKBOX, null, 'mail_webmaster_on_error'),
        	'show_errors_on_website' => array(LConfiguration::get('show_errors_on_website'), AConfiguration::TYPE_CHECKBOX, null, 'show_errors_on_website'),
        	'error_info_text_file' => array(LConfiguration::get('error_info_text_file'), AConfiguration::TYPE_TEXT, null, 'error_info_text_file')
        );
        $e->addClassSettings($this, 'error_handling', $data);
    }
    
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
        if(isset($data['mail_webmaster_on_error']))
        {
            LConfiguration::set('mail_webmaster_on_error', $data['mail_webmaster_on_error']);
        }
        if(isset($data['error_info_text_file']))
        {
            LConfiguration::set('error_info_text_file', $data['error_info_text_file']);
        }
        if(isset($data['show_errors_on_website']))
        {
            LConfiguration::set('show_errors_on_website', $data['show_errors_on_website']);
        }
    }
    
    private static function mail($kind, $code, $file, $line, $message, $stack, $workingDir)
    {
        if(LConfiguration::get('mail_webmaster_on_error') == '1')
        {
            $mail = LConfiguration::get('webmaster');
            if(!empty($mail))
            {
                mail(
                    $mail
                    ,BAMBUS_VERSION.' ['.$kind.']'
                    ,sprintf(
                        self::$err_mail
                        ,$kind
                        ,$code
                        ,$file
                        ,$line
                        ,$message
                        ,$stack
                        ,$workingDir
                        ,__FILE__
                        ,$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING']
                    ));
            }
        }
    }
    
    public static function reportErrors()
    {
        self::$report = true;
    }
    
    public static function showMessageBeforeDying()
    {
        self::$showInfoMessage = true;
    }
    
    public static function hideErrors()
    {
        self::$hideErrors = true;
    }
    
    public static function muteErrors()
    {
        self::$report = false;
    }
    
    public static function muteErrorOnce()
    {
        self::$reportSkipOnce = true;
    }
    
    public static function getLastError()
    {
        return self::$error;
    }
    
    public static function getLastErrorMessage()
    {
        return self::$errorMessage;
    }
    
    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        ob_start();
        print_r($errcontext);
        $context = ob_get_contents();
        ob_end_clean();
        $err = sprintf(
            self::$err_html
            , 'Error'
            , $errno
            , $errfile
            , $errline
            , $errstr
            , $context
            ,getcwd());
        DFileSystem::Append(SPath::LOGS.'Error.log', $err);
        self::$error = array($errno, $errstr, $errfile, $errline, $errcontext);
        self::$errorMessage = $err;
        if(self::$report && !self::$reportSkipOnce)
        {
            self::mail(
            	'Error'
                , $errno
                , $errfile
                , $errline
                , $errstr
                , $context
                ,getcwd());
            SNotificationCenter::report(
            	'warning',
                sprintf(
                	'%s %d in %s at %s: %s'
                    , 'Error'
                    , $errno
                    , $errfile
                    , $errline
                    , $errstr
                    , $context
                    ,getcwd()));
            if(!self::$hideErrors)
            {
                echo $err;
            }
        }
        self::$reportSkipOnce = false;
    }
    
    public static function reportException(Exception $e)
    {
        self::mail(
            get_class($e)
            , $e->getCode()
            , $e->getFile()
            , $e->getLine()
            , $e->getMessage()
            , $e->getTraceAsString()
            ,getcwd());
        $err = sprintf(
            self::$err_html
            , get_class($e)
            , $e->getCode()
            , $e->getFile()
            , $e->getLine()
            , $e->getMessage()
            , $e->getTraceAsString()
            ,getcwd());
        DFileSystem::Append(SPath::LOGS.'Exceptions.log', $err);
        return $err;    
    }
            
    public static function exceptionHandler(Exception $e)
    {
        $str = self::reportException($e);
        if(!self::$hideErrors)
        {
            echo $str;
        }
        if(self::$showInfoMessage)
        {
            $f = LConfiguration::get('error_info_text_file');
            if(!empty($f) && file_exists($f) && is_readable($f) && substr(basename($f),0,1) != '.')
            {
                readfile($f);
            }
        }
        exit(1);
    }
}
?>