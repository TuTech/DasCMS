<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.08.2008
 * @license GNU General Public License 3
 */
class WTemplate extends BWidget 
{
    const CLASS_NAME = "WScript";
    const AUTO = 0;
    const SYSTEM = 1;
    const CONTENT = 2;
    const STRING = 3;
     
    private $ID;
    
    private $template;
    private $scope = self::CONTENT;
    private $enviornment = array();
    private static $globalEnviornment = array();
    
    //@todo WTemplate::available(WTemplate::SYSTEM);
    
    public function __construct($target, $scope = null)
    {       
        $this->ID = ++parent::$CurrentWidgetID;
        $this->template = $target;
        if($scope != null)
        {
            $this->setScope($scope);
        }
    }

    public function setScope($scope)
    {
        if(in_array($scope, array(self::AUTO, self::SYSTEM, self::CONTENT, self::STRING)))
        {
            $this->scope = $scope;
        }
        else
        {
            throw new XUndefinedIndexException($scope);
        }
    }

    public static function globalGet($key)
    {
        if(!array_key_exists($key, self::$globalEnviornment))
        {
            throw new XUndefinedIndexException($key, 404);
        }
        return self::$globalEnviornment[$key];
    }
    
    public static function globalSet($key, $value)
    {
        self::$globalEnviornment[strval($key)] = $value;
    }  
    
    public function get($key)
    {
        if(!array_key_exists($key, $this->enviornment))
        {
            throw new XUndefinedIndexException($key, 404);
        }
        return $this->enviornment[$key];
    }
    
    public function set($key, $value)
    {
        $this->enviornment[strval($key)] = $value;
    }
    
    public function setEnvornment(array $env)
    {
        $this->enviornment = $env;
    }
    
    public function getEnvornment()
    {
        return $this->enviornment;
    }
    
    /**
     * get render() output as string
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        $this->render();
        return ob_get_clean();
    }
    
    private function getTemplateString()
    {
        $cpath = SPath::TEMPLATES.basename($this->template).'.tpl';
        $spath = SPath::SYSTEM_TEMPLATES.basename($this->template).'.tpl';
        $dat = '';
        switch ($this->scope) 
        {
            case self::STRING:
                $dat =  $this->template;
                break;
            case self::AUTO:
                if(file_exists($cpath))
                {
                    $dat = implode(file($cpath));
                }
                elseif(file_exists($spath))
                {
                    $dat = implode(file($spath));
                }
                break;
            case self::CONTENT:
                if(file_exists($cpath))
                {
                    $dat = implode(file($cpath));
                }  
                break;         
            case self::SYSTEM:
                if(file_exists($spath))
                {
                    $dat = implode(file($spath));
                }  
                break;  
            default:break;       
        }
        return $dat;
    }
    
    public function render()
    {
        $string = $this->getTemplateString();
        $data = self::$globalEnviornment;
        foreach($this->enviornment as $key => $value)
        {
            $data[$key] = $value;
        }
        foreach($data as $key => $value)
        {
            $value = mb_convert_encoding(strval($value), "UTF-8", "auto");
            $string = str_replace('{{'.$key.'}}', htmlentities($value, ENT_QUOTES, 'UTF-8'), $string);
            $string = str_replace('{'.$key.'}', $value, $string);
        }
        echo $string;
    }
    
    public function run()
    {
    }
    /**
     * return ID of primary editable element or null 
     *
     * @return string|null
     */
    public function getPrimaryInputID()
    {
        return "_".$this->ID;
    }
}
?>