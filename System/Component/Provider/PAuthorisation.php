<?php
class PAuthorisation extends BProvider 
{
    const CLASS_NAME = 'PAuthorisation';
    const DENY = false;
    const PERMIT = true;
    
    protected $Interface = 'IAuthorise';
    private static $instance = null;
    
    private static $permissions;
    
    private static $active = false;
    
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
            $relay->loadPermissions();
            self::$permissions = $relay->getPermissions();//load perms of self and anonymous
        }
    }
    
    /**
     * check if an action is permitted 
     * for the action "foo.bar.bazz.zigg.doo" the following permissions match:
     *  [0] => foo.bar.bazz.zigg.doo
     *  [1] => *
     *  [2] => *.bar.bazz.zigg.doo
     *  [3] => foo.*
     *  [4] => *.bazz.zigg.doo
     *  [5] => foo.bar.*
     *  [6] => *.zigg.doo
     *  [7] => foo.bar.bazz.*
     *  [8] => *.doo
     *  [9] => foo.bar.bazz.zigg.*
     * @return boolean
     */
    public static function request($permission)
    {
        //self::load();
        $permission = self::DENY;
        if(preg_match('/[a-z]+\.[a-z0-9-]+\.[a-z0-9-\.]+/', $permission))
        {
            $possilePermissions = array($permission, '*');
            $parts = explode('.', $permission);
            for($i = 1; $i < count($parts); $i++)
            {
                $possilePermissions[] = '*.'.implode('.', array_slice($parts, $i));
                $possilePermissions[] = implode('.', array_slice($parts, 0, $i)).'.*';
            }
            if(array_intersect($possilePermissions, self::$permissions) > 0)
            {
                $permission = self::PERMIT;
            }
        }
        return $action;
    }
}
?>