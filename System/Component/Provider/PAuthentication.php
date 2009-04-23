<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-10
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Provider
 */
class PAuthentication extends BProvider 
{
    const CLASS_NAME = 'PAuthentication';
    const FAILED_LOGIN = -1;
    const NO_LOGIN = 0;
    const VALID_USER = 1;
    const CONTINUED_SESSION = 2;
    protected $Interface = 'IAuthenticate';
    private static $instance = null;
    
    private static $userID = '';
    private static $userName = '';
    private static $userEmail = '';
    private static $userStatus;
    
    private static $daemonRun = false;
    
    private static $active = false;
    private static $implied = false;
    
    private function __construct()
    {
        
    }
    
    /**
     * @return PAuthentication
     */
    private static function instance()
    {
        if(self::$instance == null)
        {
            self::$instance = new PAuthentication();
        }
        return self::$instance;
    }

    private static function checkActive()
    {
        if(self::$implied)
        {
            if(headers_sent())
            {
                self::$userStatus = self::NO_LOGIN;
                self::$active = true;
            }
            else
            {
                self::required();
            }
        }
        if(!self::$active)
        {
            throw new Exception(self::CLASS_NAME.'::required() must be called before this function');
        }
    }
    
    public static function daemonRun()
    {
        if(!self::$active)
        {
            self::$userID = 0;
            self::$userName = 'system';
            self::$userEmail = '';
            self::$userStatus = self::VALID_USER;
            self::$daemonRun = true;
            self::$active = true;
        }
    }
    
    public static function isDaemon()
    {
        return self::$daemonRun;
    }
    
    public static function implied()
    {
        if(!self::$active)
        {
            self::$implied = true;
        }
    }
    
    /**
     * function to mark a "authentication required" section
     * @return void
     */
    public static function required()
    {
        if(!self::$active)
        {
            RSession::start();
            //get the class assigned to do the authentication
            try
            {
                $implementor = self::instance()->getImplementor();
            }
            catch(XUndefinedException $e)
            {
                throw new XPermissionDeniedException('no authentication class found');
            }
            
            //stop this from running again
            self::$active = true;
            
            //instanciate class
            $relay = BObject::InvokeObjectByDynClass($implementor);
            
            //check class
            if(!$relay instanceof IAuthenticate)
            {
                throw new XPermissionDeniedException('authentication class failed');
            }
            
            //run class
            $relay->authenticate();
            self::$userStatus = $relay->getAuthenticationState();
            
            //log failed attempts
            if(self::$userStatus == self::FAILED_LOGIN)
            {
                QPAuthentication::logAccess(
                    RServer::getNumericRemoteAddress(), 
                    $relay->getAttemptedUserID(), 
                    self::$userStatus >= self::VALID_USER 
                );
            }
            
            //check the failed login count of the last 15 minutes
            $res = QPAuthentication::latestFails(RServer::getNumericRemoteAddress(), $relay->getAttemptedUserID());
            list($fails) = $res->fetch();
            $res->free();
            
            //deny access if count exceeds 5 even if login was correct
            if($fails > 5)
            {
                SNotificationCenter::report(
                    SNotificationCenter::TYPE_WARNING, 
                    'exceeded_5_failed_login_attempts_in_the_last_15_minutes_access_denied'
                );
                
                //mark as failed
                self::$userStatus = self::FAILED_LOGIN;
            }
            else
            {
                //everything ok - get the rest
                self::$userID = $relay->getUserID();
                self::$userName = $relay->getUserName();
                self::$userEmail = $relay->getUserEmail();
                
                //punish only new logins - not continued session
                if(self::$userStatus != self::CONTINUED_SESSION && self::$userStatus != self::NO_LOGIN)
                {
                    //get login history
                    $res = QPAuthentication::countUserFails($relay->getAttemptedUserID());
                    list($userFails) = $res->fetch();
                    $res->free();
                    $res = QPAuthentication::countIPAdrFails(RServer::getNumericRemoteAddress());
                    list($ipadrFails) = $res->fetch();
                    $res->free();
                    
                    //calculate punishment delay
                    $punishment = min(10, $userFails)+min(15, $ipadrFails*2);
                    if($punishment)
                    {
                        SNotificationCenter::report(
                            SNotificationCenter::TYPE_WARNING, 
                            'output_delayed_'.$punishment.'_seconds'
                        );
                        sleep($punishment);
                    }
                }
            }
        }
    }
    
    /**
     * get status of authentication FAILED_LOGIN/NO_LOGIN/VALID_USER/CONTINUED_SESSION
     * @return string
     */
    public static function getAuthenticationState()
    {
        self::checkActive();
        return self::$userStatus;
    }
    
    /**
     * returns users login name
     * @return string
     */
    public static function getUserID()
    {
        self::checkActive();
        return self::$userID;
    }
    
    /**
     * returns users real/full name
     * @return string
     */
    public static function getUserName()
    {
        self::checkActive();
        return self::$userName;  
    }
    
    /**
     * returns users email address
     * @return string
     */
    public static function getUserEmail()
    {
        self::checkActive();
        return self::$userEmail;
    }
    
    /**
     * auth successful
     * @return boolean
     */
    public static function isAuthenticated()
    {
        self::checkActive();
        return self::$userStatus >= self::VALID_USER;
    }
        
}
?>