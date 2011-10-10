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
class PAuthorisation 
    extends BProvider 
    implements 
        Interface_Singleton, IProvider
{
    const ROLE_ADMINISTRATOR = 'administrator';
    const ROLE_DAEMON = self::ROLE_ADMINISTRATOR;
    const ROLE_EDITOR = 'editor';
    const ROLE_USER = 'user';
    const ROLE_REGISTERED_VISITOR = 'registered_visitor';
    const ROLE_VISITOR = 'visitor';
    
    const CLASS_NAME = 'PAuthorisation';
    const DENY = false;
    const PERMIT = true;
    
    private static $instance = null;

    private static $roleStatus = array(
        self::ROLE_VISITOR => 1,
        self::ROLE_REGISTERED_VISITOR => 2,
        self::ROLE_USER => 3,
        self::ROLE_EDITOR => 4,
        self::ROLE_ADMINISTRATOR => 5
    );
    
    private static $permissions;
    private static $objectPermissions;
    private static $primaryGroup;
    private static $groups;
    private static $role;
    
    private static $active = false;
    private static $cache = array();
    
    /**
     * @return PAuthorisation
     */
    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new PAuthorisation();
        }
        return self::$instance;
    }
    
    private function __construct()
    {
		$this->interface = 'IAuthorize';
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
                self::$role = self::ROLE_DAEMON;
            }
            else
            {    
                try
                {
                    $implementor = self::getInstance()->getImplementor();
                }
                catch(Exception $e)
                {
                    throw new AccessDeniedException('no authentication class found');
                }
                self::$active = true;
                $relay = BObject::InvokeObjectByDynClass($implementor);
                if(!$relay instanceof IAuthorize)
                {
                    throw new AccessDeniedException('authentication class failed');
                }
                self::$permissions = $relay->getPermissions();//load perms of self and anonymous
                self::$objectPermissions = $relay->getObjectPermissions();//load perms of self and anonymous
                self::$groups = $relay->getGroups();//load perms of self and anonymous
                self::$primaryGroup = $relay->getPrimaryGroup();//load perms of self and anonymous
                self::$role = $relay->getRole();
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
     * @throws ArgumentException
     */
    public static function has($permission, $object = null)
    {
        if(!preg_match('/[a-z]+\.[a-z0-9-]+\.[a-z0-9-\.]+/', $permission))
        {
            throw new ArgumentException('invalid permission '.$permission);
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
     * this will fail with a AccessDeniedException if action is not permitted
     * @throws AccessDeniedException
     */
    public static function requires($permission, $object = null)
    {
        if(!$this->has($permission, $object))
        {
            throw new AccessDeniedException($permission.($object == null ? '' : ' for '.$object));
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
        
    public static function getRole()
    {
        self::load();
        return self::$role;
    }
    
    /**
     * retruns int smaller than 0 if $role is higer than the role of the loggin in user,
     * 0 if they are on eqal level and greater than 0 if the logged in user has a higher role
     * @param string $role
     * @return int
     */
    public static function comparedToRole($role)
    {
        if(!isset(self::$roleStatus[$role]))
        {
            throw new ArgumentException('invalid role');
        }
        return self::$roleStatus[self::getRole()] - self::$roleStatus[$role];
    }
    
    public static function isInGroup($group)
    {
        return in_array($group, self::$groups);
    }
}
?>