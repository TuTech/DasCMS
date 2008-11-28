<?php
abstract class _
{
    protected function debug_log($msg)
    {
        printf("[%s] %s\n", get_class($this), $msg);
    }
}
?>