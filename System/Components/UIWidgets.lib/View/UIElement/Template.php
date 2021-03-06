<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-08-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_Template extends _View_UIElement 
{
    const CLASS_NAME = "View_UIElement_Template";
    const AUTO = 0;
    const SYSTEM = 1;
    const CONTENT = 2;
    const STRING = 3;
     
    private $id;
    
    private $template;
    private $scope = self::CONTENT;
    private $environment = array();
    private static $globalEnviornment = array();
    
    public function __construct($target, $scope = null)
    {       
        $this->id = ++parent::$CurrentWidgetID;
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
        if(!array_key_exists($key, $this->environment))
        {
            throw new XUndefinedIndexException($key, 404);
        }
        return $this->environment[$key];
    }
    
    public function set($key, $value)
    {
        $this->environment[strval($key)] = $value;
    }
    
    public function setEnvironment(array $env)
    {
        $this->environment = $env;
    }
    
    public function getEnvironment()
    {
        return $this->environment;
    }
    
    public static function getGlobalEnvironment()
    {
        return self::$globalEnviornment;
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
        $cpath = Core::PATH_TEMPLATES.basename($this->template).'.tpl';
        $spath = Core::PATH_SYSTEM_TEMPLATES.basename($this->template).'.tpl';
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
    
    public function renderString(array $withData = array())
    {
        $string = $this->getTemplateString();
        $data = self::$globalEnviornment;
        foreach($this->environment as $key => $value)
        {
            $data[$key] = $value;
        }
        foreach($withData as $key => $value)
        {
            $data[$key] = $value;
        }
        foreach($data as $key => $value)
        {
            $value = mb_convert_encoding(strval($value), CHARSET, "auto");
            $string = str_replace('{{'.$key.'}}', String::htmlEncode($value), $string);
            $string = str_replace('{'.$key.'}', $value, $string);
        }
        return $string;
    }
    
    public function render(array $withData = array())
    {
        echo $this->renderString($withData);
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
        return "_".$this->id;
    }
    
    public static function renderOnce($tpl, $type)
    {
        $tpl = new View_UIElement_Template($tpl, $type);
        echo $tpl->render();
		flush();
    }
}
?>