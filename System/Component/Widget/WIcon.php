<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 01.09.2008
 * @license GNU General Public License 3
 */
class WIcon extends BWidget 
{
    const EXTRA_SMALL = 16;
    const SMALL = 22;
    const MEDIUM = 32;
    const LARGE = 48;
    
    private $size = 22;
    private $text = '';
    private $icon = '';
    private $type = 'action';
    private static $sizes = array(16 => 'extra-small', 22 => 'small', 32 => 'medium', 48 => 'large');
    
    public function __construct($icon, $text, $size = null, $type = null)
    {
        $parts = explode('-', $icon);
        if(count($parts) > 1)
        {
            $type = array_shift($parts);
            $icon = implode('-', $parts);
        }
        if(in_array($type, array('action', 'animation', 'app', 'devive', 'emblem', 'mimetype', 'place', 'status')))
        {
            $this->type = $type;
        }
        $this->icon = $icon;
        $this->text = mb_convert_encoding($text, 'UTF-8');
        $this->setSize($size);
    }
    
    public function getPath()
    {
        return self::pathFor($this->icon, $this->type, $this->size);
    }
    
    /**
     * @param string $icon
     * @param string $type
     * @param int $size
     */
    public static function pathFor($icon, $type = 'action', $size = null)
    {
        $type = substr($type, -1) == 's' ? $type : $type.'s';
        $size = in_array($size, array_keys(self::$sizes)) ? $size : 22;
        $tango  = SPath::SYSTEM_ICONS.self::$sizes[$size].'/'.$type.'/'.$icon.'.png';
        $legacy = SPath::SYSTEM_ICONS.'../'.$size.'x'.$size.'/'.$type.'/'.$icon.'.png';
        if(!file_exists($tango) && file_exists($legacy))
        {
            return $legacy;
        }
        else
        {
            return $tango;
        }
    }
    
    /**
     * @param string $icon
     * @param string $type
     * @param int $size
     */
    public static function pathForMimeIcon($icon, $size = null)
    {
        $type = 'mimetypes';
        $icon = str_replace('+', '_', strtolower($icon));
        list($spec, $name) = explode('/', $icon);
        $check = array($spec.'-'.$name, $name);
        $tmp = explode('-',$name);//x-pdf to pdf
        $check[] = array_pop($tmp);
        $path = array();
        $path[0] = sprintf('%s/%s/mimetypes/%%s.png', SPath::SYSTEM_ICONS, self::$sizes[$size]);
        $path[1] = sprintf('%s../%dx%d/mimetypes/%%s.png', SPath::SYSTEM_ICONS,$size,$size);
        $found = null;
        foreach ($check as $item) 
        {
            foreach ($path as $ptpl) 
            {
            	$p = sprintf($ptpl, $item);
            	if(file_exists($p))
            	{
            	    return $p;
            	}
            }
        }
        return sprintf('%s../%dx%d/mimetypes/file.png', SPath::SYSTEM_ICONS,$size,$size);
    }
    
    public function getType()
    {
        return  $this->type;
    }
    
    public function getSize()
    {
        return $this->size;
    }
    
    public function setSize($size)
    {
        if(in_array($size, array(self::EXTRA_SMALL, self::SMALL, self::MEDIUM, self::LARGE)))
        {
            $this->size = $size;
        }
    }
    
    public function asSize($size)
    {
        $this->setSize($size);
        return $this;
    }
    
    public function __toString()
    {
        return sprintf(
            "<img src=\"%s\" alt=\"%s\" title=\"%s\" />"
            ,$this->getPath()
            ,htmlentities($this->text, ENT_QUOTES, 'UTF-8')
            ,htmlentities($this->text, ENT_QUOTES, 'UTF-8')
        );
    }
}
?>