<?php
interface Interface_View_Atom
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