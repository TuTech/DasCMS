<?php
class _XML_Atom_Content extends _XML_Atom 
{
    protected static $_attributes = array(
        'type' => _XML::NONE_OR_MORE,
    );
    protected $type = 'text'; 
    protected $data;

    protected function getElementParsers()
    {
        return array();
    }
    
}
?>