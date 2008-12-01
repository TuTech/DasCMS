<?php
/**
 * Atom content 
 */
class _XML_Atom_Content extends _XML_Atom 
{
    protected static $_attributes = array(
        'type' => _XML::NONE_OR_MORE,
    );
    protected $type = 'text'; 
    protected $data;
    
    public function getType()
    {
        return $this->type;
    }
    
    public function __toString()
    {
        return strval($this->data);
    }
}
?>