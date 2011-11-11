<?php
interface Interface_View_DisplayAtom
{
    /**
     * @return _XML_Atom
     */
    public function toAtom();
    
    /**
     * @return string
     */
    public function getAtomTag();
}
?>