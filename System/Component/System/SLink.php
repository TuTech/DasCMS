<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-04
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SLink 
    extends 
        BSystem 
{
    private static $base = null;
    private static $getData = null;
    
    /**
     * @return boolean
     */
    public static function isManagement()
    {
        $path = explode('/', dirname($_SERVER['SCRIPT_NAME']));
        return (count($path) > 0 && $path[count($path)-1] == 'Management');
    }
    
    /**
     * link base
     * 
     * @return string
     */
    public static function selfURI()
    {
        global $_SERVER;
        $http = (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
        $server = $_SERVER['SERVER_NAME'];
        $script = $_SERVER['SCRIPT_NAME'];
        $port = ($_SERVER['SERVER_PORT'] == 80 || ($_SERVER['SERVER_PORT'] == 443 && $http == 'https')) ? '' : ':'.$_SERVER['SERVER_PORT'];
        $pathinfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        return sprintf('%s://%s%s%s%s', $http, $server, $port, $script, $pathinfo);
    }
    /**
     * link base
     * 
     * @return string
     */
    public static function base()
    {
        global $_SERVER;
        if(self::$base == null)
        {
            //get infos
            $script = $_SERVER['SCRIPT_NAME'];
            $server = $_SERVER['SERVER_NAME'];
            $http = (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
            $port = ($_SERVER['SERVER_PORT'] == 80 || ($_SERVER['SERVER_PORT'] == 443 && $http == 'https')) ? '' : ':'.$_SERVER['SERVER_PORT'];
            //calculate path to cms root
            $path = explode('/', dirname($script));
            $optimizedPath = array();
            if(self::isManagement())
            {
                array_pop($path);
            }
            foreach ($path as $step) 
            {
            	if($step != '' && $step != '.')
            	{
            	    $optimizedPath[] = $step;
            	}
            }
            $newPath = implode('/', $optimizedPath);
            $newPath = strlen($newPath) > 0 ? $newPath.'/' : '';
            self::$base = sprintf('%s://%s%s/%s', $http, $server, $port, $newPath);
        }
        return self::$base;
    }
    
   /**
    * read data from get request 
    */
   private static function loadInput()
    {
        if(self::$getData == null)
        {
            self::$getData = array();
            $get = RURL::data();
            foreach ($get as $k => $v) 
            {
                if(substr($k,0,1) != '_')
                {
                    self::$getData[$k] = $v;
                }
            }
        }
    }
    
    /**
     * combine given data with current request data
     *
     * @param array $withAdditionalData
     * @return array
     */
    private static function merge(array $withAdditionalData)
    {
        self::loadInput();
        $data = self::$getData;
        foreach ($withAdditionalData as $k => $v) 
        {
            if($v === null)
            {
                unset($data[$k]);
            }
            else
            {
                $data[$k] = $v;
            }
        } 
        return $data;
    }
    
    /**
     * build standard url with given and current data
     *
     * @param array $withAdditionalData
     * @return string
     */
    public static function buildURL(array $withAdditionalData = array(), $clean = false)
    {
        if($clean)
        {
            $data = $withAdditionalData;
        }
        else
        {
            $data = self::merge($withAdditionalData);
        }
        $tok = '?';
        $url = ''; 
        foreach ($data as $k => $v) 
        {
        	$url .= sprintf('%s%s=%s', $tok, urlencode($k), urlencode($v));
        	$tok = '&';
        }
        return $url;
    }
    
    /**
     * build path-style url with given and current data
     *
     * @param array $withAdditionalData
     * @return string
     */
    public static function buildPath(array $withAdditionalData = array(), $clean = false)
    {
        if($clean)
        {
            $data = $withAdditionalData;
        }
        else
        {
            $data = self::merge($withAdditionalData);
        }
        $url = ''; 
        foreach ($data as $k => $v) 
        {
            $url .= sprintf('/%s/%s', urlencode($k), urlencode($v));
        }
        return $url;
    }

    /**
     * use appropriate link building method
     *
     * @param array $withAdditionalData
     * @return string
     */
    public static function link(array $withAdditionalData = array(), $file = '', $clean = false)
    {
        $wfu = LConfiguration::get('wellformed_urls');
        if(self::isManagement())
        {
            $url =  'Management/'.$file.self::buildURL($withAdditionalData, $clean);
        }
        elseif(empty($wfu))
        {
            $url =  $file.self::buildURL($withAdditionalData, $clean);
        }
        else
        {
            $url =  ($file == '' ? 'index.php' : $file).self::buildPath($withAdditionalData, $clean);
        }
        return $url;
    }
    
    public static function set($k, $v)
    {
        if($v == null)
        {
            unset(self::$getData[$k]);
        }
        else
        {
            self::$getData[$k] = $v;
        }
    }
    
    public static function get($k)
    {
        return isset(self::$getData[$k]) ? (self::$getData[$k]) : '';
    }
}

?>