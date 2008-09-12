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
            if(!$relay instanceof IAuthorize)
            {
                throw new XPermissionDeniedException('authentication class failed');
            }
            self::$permissions = $relay->getPermissions();//load perms of self and anonymous
        }
    }
    
    private function calculateRight($permission, $right)
    {
        if(array_key_exists($permission, self::$permissions))
        {
            if($right == null || self::$permissions[$permission] == self::DENY)
            {
                $right = self::$permissions[$permission];
            }
        }
        return $right;
    }
    
    /**
     * check if an action is permitted 
     * for the action "foo.bar.bazz.zigg.doo" the following permissions match:
     *  [0] => foo.bar.bazz.zigg.doo
     *  [1] => *.bar.bazz.zigg.doo
     *  [2] => foo.bar.bazz.zigg.*
     *  [3] => *.bazz.zigg.doo
     *  [4] => foo.bar.bazz.*
     *  [5] => *.zigg.doo
     *  [6] => foo.bar.*
     *  [7] => *.doo
     *  [8] => foo.*
     *  [9] => *
     * @return boolean
     * @throws XArgumentException
     */
    public static function request($permission)
    {
        //self::load();
        $result = null;
        if(preg_match('/[a-z]+\.[a-z0-9-]+\.[a-z0-9-\.]+/', $permission))
        {
            if(array_key_exists($permission, self::$permissions))
            {
                return self::$permissions[$permission];
            }
            $parts = explode('.', $permission);
            for($i = 1; $i < count($parts); $i++)
            {
                $result = $this->calculateRight('*.'.implode('.', array_slice($parts, $i)), $result);
                $result = $this->calculateRight(implode('.', array_slice($parts, 0, $i)).'.*', $result);
            }
            $result = $this->calculateRight('*', $result);
        }
        else
        {
            throw new XArgumentException('invalid permission '.$permission);
        }
        return ($result == null) ? (self::DENY) : $result;
    }
}
?>