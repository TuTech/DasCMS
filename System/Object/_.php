<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 */
abstract class _
{
    protected function debug_log($msg)
    {
        if(defined('BAMBUS_DEBUG'))
        {
            printf("\n<!-- [%s] %s -->\n", get_class($this), $msg);
        }
    }
}
?>