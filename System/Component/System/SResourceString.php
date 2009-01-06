<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 06.01.2009
 * @license GNU General Public License 3
 */
class SResourceString
    extends 
        BSystem 
{    
    public static function get($resource, $key)
    {
        $resourceFile = SPath::SYSTEM_RESOURCE_STRINGS.$resource.'.phpdata';
        if(file_exists($resourceFile))
        {
            $data = DFileSystem::LoadData($resourceFile);
            if(is_array($data) && array_key_exists($key, $data))
            {
                return $data[$key];
            }
            else
            {
                throw new XUndefinedIndexException('key not in resource file');
            }
        }
        else
        {
            throw new XFileNotFoundException('resource file not found');
        }
    }
}
?>