<?php
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