<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-01-06
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class SResourceString
{    
    public static function get($resource, $key)
    {
        $resourceFile = Core::PATH_SYSTEM_RESOURCE_STRINGS.$resource.'.php';
        if(file_exists($resourceFile))
        {
            $data = Core::FileSystem()->loadEncodedData($resourceFile);
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