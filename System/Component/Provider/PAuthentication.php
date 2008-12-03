<?php
class PAuthentication extends BProvider 
{
    const CLASS_NAME = 'PAuthentication';
    const FAILED_LOGIN = -1;
    const NO_LOGIN = 0;
    const VALID_USER = 1;
    
    protected $Interface = 'IAuthenticate';
    private static $instance = null;
    
    private static $userID;
    private static $userName;
    private static $userEmail;
    private static $userStatus;
    
    private static $daemonRun = false;
    
    private static $active = false;
    
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
    
    /**
     * function to mark a "authentication required" section
     * @return void
     */
    public static function required()
    {
        if(!self::$active)
        {
            try
            {
                $implementor = self::instance()->getImplementor();
            }
            catch(XUndefinedException $e)
            {
                throw new XPermissionDeniedException('no authentication class found');
            }
            self::$active = true;
            $relay = BObject::InvokeObjectByDynClass($implementor);
            if(!$relay instanceof IAuthenticate)
            {
                throw new XPermissionDeniedException('authentication class failed');
            }
            $relay->authenticate();
            self::$userID = $relay->getUserID();
            self::$userName = $relay->getUserName();
            self::$userEmail = $relay->getUserEmail();
            self::$userStatus = $relay->getAuthenticationState();
        }
    }
    
    /**
     * get status of authentication FAILED_LOGIN/NO_LOGIN/VALID_USER
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
        return self::$userStatus == self::VALID_USER;
    }
        
}
?>