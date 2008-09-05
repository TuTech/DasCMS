<?php
class SLink extends BSystem 
{
    private static $base = null;
    private static $current = null;
    private static $getData = null;
    
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
            
            //calculate path to cms root
            $path = explode('/', dirname($script));
            $optimizedPath = array();
            if(constant('BAMBUS_ACCESS_TYPE') == 'management')
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
            self::$base = sprintf('%s://%s/%s/', $http, $server, implode('/', $optimizedPath));
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
        $data = self::$current;
        foreach ($withAdditionalData as $k => $v) 
        {
            if($v == null)
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
    public static function buildURL(array $withAdditionalData = array())
    {
        $data = self::merge($withAdditionalData);
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
    public static function buildPath(array $withAdditionalData = array())
    {
        $data = self::merge($withAdditionalData);
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
    public static function link(array $withAdditionalData = array(), $file = '')
    {
        $wfu = LConfiguration::get('wellformed_urls');
        if(constant('BAMBUS_ACCESS_TYPE') == 'management')
        {
            $url =  'Management/'.$file.self::buildURL($withAdditionalData);
        }
        elseif(empty($wfu))
        {
            $url =  $file.self::buildURL($withAdditionalData);
        }
        else
        {
            $url =  ($file == '' ? 'index.php' : $file).self::buildPath($withAdditionalData);
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