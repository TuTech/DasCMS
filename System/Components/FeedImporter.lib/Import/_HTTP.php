<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 */
abstract class _Import_HTTP 
{
    /**
     * @return string
     */
    protected function httpGet($uri)
    {
        $data = '';
        $fp = fopen($uri, 'r');
        if(!$fp)
        {
            throw new XFileNotFoundException('could not open uri', $uri);
        }
        while(!feof($fp))
        {
            $data .= fread($fp, 1024);
        }
        fclose($fp);
        return $data;
    }
}
?>