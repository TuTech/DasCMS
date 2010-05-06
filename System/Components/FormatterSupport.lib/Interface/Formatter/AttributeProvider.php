<?php
interface Interface_Formatter_AttributeProvider
{
    public function getRestoreHash();
    public static function restoreFromHash($hash);
}
?>