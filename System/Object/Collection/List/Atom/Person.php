<?php
class Collection_List_Atom_Person extends _Collection_List_Atom 
{
    public function __construct(array $items)
    {
        parent::__construct($items);
    }
        
    /**
     * @return XML_Atom_Person
     */
    public function get()
    {
        return parent::get();
    }
    
    public function add($element)
    {
        $this->assertType($element);
        $this->items[] = $element;
    }
    
    protected function typeMatch($element)
    {
        return $element instanceof XML_Atom_Person;
    }
}
?>