<?php
/**
 * @package Bambus
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 11.09.2007
 * @license GNU General Public License 3
 */
class SParser extends BSystem implements IShareable
{   
    function parse($utf8_string, $Object = NULL)
    {
        $dbg = false;
        //sanity check
        if(mb_strlen($utf8_string, 'UTF-8') > 2
            && strpos($utf8_string, '{') !== false
            && strpos($utf8_string, '}') !== false
        )
        {
            //worth parsing
            $cmdStarts = array();
            $cmdPairs = array();
            $end = false;
            $found = '';
            $foundAt = 0;
            while(!$end)
            {
                //because we search binary for ascii chars we should not need mb_* functions
                if($dbg)
                {
                    echo 'going into loop with $foundAt: '.$foundAt;
                }
                //find next occurance of { or }
                $cbegin = strpos($utf8_string, '{', $foundAt+1);
                $cend = strpos($utf8_string, '}', $foundAt+1);
                if($cbegin === false && $cend === false)
                {
                    $end = true;
                }
                elseif($cbegin === false || $cend === false)
                {
                    $foundAt = max($cbegin, $cend);
                }
                else
                {
                    $foundAt = min($cbegin, $cend);
                }
                $found = substr($utf8_string, $foundAt, 1);
                if($dbg)    
                {
                    printf('<p>found %s at %d</p>', $found, $foundAt);
                }
                //order commands
                if($found == '{')
                {
                    //add to possible command start stack
                    array_push($cmdStarts, $foundAt);
                }
                elseif($found == '}' && count($cmdStarts) > 0)
                {
                    //pair end to start point of cmd
                    $cmdPairs[] = array(array_pop($cmdStarts), $foundAt);
                }
            }
            
            $expressions = array(); //nested commands
            $expression_depth = array(); //nested commands
            $commands = array();
            $expression_range = array();
            
            foreach($cmdPairs as $pair)
            {
                list($begin, $end) = $pair;
                $substr = substr($utf8_string, $begin+1, $end-$begin-1);
                if(strpos($substr, '{') !== false)
                {
                    //expressions - no supported yet
                    $id = md5($substr);
                    $expressions[$id] = $substr;
                    $expression_range[$id] = $pair;
                    $expression_depth[$id] = strlen($substr) - strlen(str_replace('{', '', $substr));//how deep are the nested commands
                }
                else
                {
                    //simple commands
                    if(!isset($commands[$substr]))
                    {
                        $commands[$substr] = $this->exec_cmd($substr, $Object);
                    }
                }
            }
            
            foreach($commands as $command => $result)
            {
                $utf8_string = str_replace('{'.$command.'}', $result, $utf8_string);
                
            }           
        }
        return $utf8_string; 
    }

    private function cmsCallBack($string)
    {
        switch(strtolower($string))
        {
            case 'version':         
                return BAMBUS_VERSION;
            case 'rootdir':         
                return BAMBUS_CMS_ROOTDIR;
            case 'diskspace':
            case 'freediskspace':
            case 'diskfreespace':   
                return DFileSystem::formatSize(disk_free_space(BAMBUS_CMS_ROOTDIR));
            case 'memoryusage':
            case 'memusage':        
                return DFileSystem::formatSize(memory_get_usage(true));
            case 'starttime':
            case 'execstart':       
                return BAMBUS_EXEC_START;
            case 'gentime':
            case 'runtime':         
                return round((microtime(true) - BAMBUS_EXEC_START),2);
            case 'bambuslogo':      
                return 'System/Images/BambusCMSLogo.png';
            case 'applicationlogo': 
                return BAMBUS_APPLICATION_ICON;
            case 'applicationtitle': 
                return BAMBUS_APPLICATION_TITLE;
            case 'applicationtablogo':
                return BAMBUS_APPLICATION_TAB_ICON;
            case 'applicationtabtitle':
                return BAMBUS_APPLICATION_TAB_TITLE;
            case 'currentobject':   
                return BAMBUS_CURRENT_OBJECT;
            case 'currentobjecttitle':
                return substr(BAMBUS_CURRENT_OBJECT,0,strlen(DFileSystem::suffix(BAMBUS_CURRENT_OBJECT))*-1-1);
            case 'currentobjecticon':
                return WIcon::pathFor(DFileSystem::suffix(BAMBUS_CURRENT_OBJECT),'mimetype');
            default:                
                return '';
        }
    }
    
    private function exec_cmd($string, $Object = NULL)
    {
        $SCI = SComponentIndex::alloc()->init();
        //if($Object != NULL)
        {
            if(strpos($string, ':')!= false)
            {
                list($class, $prop) = explode(':',$string);
                if($class == 'NTreeNavigation'|| $class == 'TreeNavigation'|| $class == 'navigation')
                {
                    if(NTreeNavigation::exists($prop))
                    {
                        return NTreeNavigation::alloc()->init()->navigatieWith($prop);
                    }
                    return '';  
                }
                elseif($class == 'ListNavigation' || $class == 'NListNavigator')
                {
                    return NListNavigation::alloc()->init()->navigateWith($prop);
                }
                elseif($class === 'cms')
                {
                    return $this->cmsCallBack($prop);
                }
                elseif($class === 'SLink')
                {
                    switch ($prop) 
                    {
                        case 'base':
                            return SLink::base();
                            break;
                        case 'link':
                            return SLink::link();
                        break;
                    }
                }
                elseif(class_exists($class, true))
                {
                    $obj = new $class();
                    if($obj instanceof IShareable)
                    {
                        $obj = $obj->alloc();
                        $obj->init();
                    }
                    if(isset($obj->{$string}))
                    {
                        return $obj->{$string};
                    }
                }
                else
                {
                    return LConfiguration::get($prop);
                }
            }
            elseif(ctype_alnum($string))
            {   
                try
                {
                    if($SCI->IsExtension($string, 'BWidget'))
                    {
                        return new $string();
                    }
                }
                catch (Exception $e)
                {
                    
                }
            }
        
            if(isset($Object->{$string}))
            {
                return $Object->{$string};
            }
            elseif(method_exists($Object, $string))
            {
                return $Object->{$string}();
            }
            else
            {
                LConfiguration::get($string);
            }
        }
        return ($string);
    }

    //IShareable
    const CLASS_NAME = 'SParser';
    public static $sharedInstance = NULL;
    private static $initializedInstance = false;
    public static function alloc()
    {
        $class = self::CLASS_NAME;
        if(self::$sharedInstance == NULL && $class != NULL)
        {
            self::$sharedInstance = new $class();
        }
        return self::$sharedInstance;
    }
    
    public function init()
    {
        if(!self::$initializedInstance)
        {
            self::$initializedInstance = true;
        }
        return $this;
    }
    //end IShareable
}
?>