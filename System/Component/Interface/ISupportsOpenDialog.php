<?php
interface ISupportsOpenDialog
{
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideOpenDialogData(array $namedParameters);
    
    /**
     * opened object 
     * @return string|null 
     */
    public function getOpenDialogTarget();
}
?>