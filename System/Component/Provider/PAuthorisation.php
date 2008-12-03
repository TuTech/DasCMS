<?php
class PAuthorisation extends BProvider 
{
    const CLASS_NAME = 'PAuthorisation';
    const DENY = false;
    const PERMIT = true;
    
    protected $Interface = 'IAuthorise';
    private static $instance = null;
    
    private static $permissions;
    private static $objectPermissions;
    private static $primaryGroup;
    private static $groups;
    
    private static $active = false;
    private static $cache = array();
    
    private function __construct()
    {
    }
    
    /**
     * @return PAuthorisation
     */
    private static function instance()
    {
        if(self::$instance == null)
        {
            self::$instance = new PAuthorisation();
        }
        return self::$instance;
    }

    private static function load()
    {
        if(!self::$active)
        {
            if(PAuthentication::isDaemon())
            {
                self::$permissions = array('*' => PAuthorisation::PERMIT);//load perms of self and anonymous
                self::$objectPermissions = array();//load perms of self and anonymous
                self::$groups = array();//load perms of self and anonymous
                self::$primaryGroup = '';//load perms of self and anonymous
                self::$active = true;
            }
            else
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
                if(!$relay instanceof IAuthorize)
                {
                    throw new XPermissionDeniedException('authentication class failed');
                }
                self::$permissions = $relay->getPermissions();//load perms of self and anonymous
                self::$objectPermissions = $relay->getObjectPermissions();//load perms of self and anonymous
                self::$groups = $relay->getGroups();//load perms of self and anonymous
                self::$primaryGroup = $relay->getPrimaryGroup();//load perms of self and anonymous
            }
        }
    }
    
    /**
     * check if an action is permitted 
     * for the action "foo.bar.bazz.zigg.doo" the following permissions match:
     *  foo.bar.bazz.zigg.doo
     *  *.bar.bazz.zigg.doo
     *  *.bazz.zigg.doo
     *  *.zigg.doo
     *  *.doo
     *  foo.bar.bazz.zigg.*
     *  foo.bar.bazz.*
     *  foo.bar.*
     *  foo.*
     *  *
     * @return boolean
     * @throws XArgumentException
     */
    public static function has($permission, $object = null)
    {
        if(!preg_match('/[a-z]+\.[a-z0-9-]+\.[a-z0-9-\.]+/', $permission))
        {
            throw new XArgumentException('invalid permission '.$permission);
        }
        
        self::load();
        $uid = sprintf('%s(%s)',$permission, ($object == null ? '-' : $object));
        if(!array_key_exists($uid, self::$cache))
        {
            $result = null;
    
            //build array for possible rights. most significant first
            $test = array($permission);
            $parts = explode('.', $permission);
            //*.bar.bazz.zigg.doo -> *.doo
            for($i = 1; $i < count($parts); $i++)
            {
                $test[] = '*.'.implode('.', array_slice($parts, $i));
            }
            //foo.bar.bazz.zigg.* -> foo.*
            for($i = 1; $i < count($parts); $i++)
            {
               $test[] = implode('.', array_slice($parts, 0, $i)).'.*';
            }
            //global wildcard
            $test[] = '*';
            
            //check for special object permissions
            if($object != null && array_key_exists($object, self::$objectPermissions))
            {
                $objPerms = self::$objectPermissions[$object];
            }
            else
            {
                $objPerms = array();
            }
            
            //check permissions 
            foreach ($test as $perm) 
            {
                if(array_key_exists($perm, $objPerms))
                {
                    $result = $objPerms[$perm];
                    break;
                }               
                if(array_key_exists($perm, self::$permissions))
                {
                    $result = self::$permissions[$perm];
                    break;
                }
            }
            $result = ($result == null) ? (self::DENY) : $result;
            self::$cache[$uid] = $result;
        }
        else
        {
            $result = self::$cache[$uid];
        }
        return $result;
    }
    
    /**
     * this will fail with a XPermissionDeniedException if action is not permitted
     * @throws XPermissionDeniedException
     */
    public static function requires($permission, $object = null)
    {
        if(!$this->has($permission, $object))
        {
            throw new XPermissionDeniedException($permission.($object == null ? '' : ' for '.$object));
        }
    }
    
    public static function getPrimaryGroup()
    {
        self::load();
        return self::$primaryGroup;
    }
    
    public static function getGroups()
    {
        self::load();
        return self::$groups;
    }
    
    public static function isInGroup($group)
    {
        return in_array($group, self::$groups);
    }
}
?>