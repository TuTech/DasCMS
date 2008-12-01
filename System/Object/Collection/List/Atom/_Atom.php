<?php
abstract class _Collection_List_Atom extends _Collection_List  
{
    protected function __construct(array $items = array())
    {
        $this->items = $this->assertArrayType($items);
    }
    
    abstract protected function typeMatch($element);
    
    protected function assertType($element)
    {
        if(!$this->typeMatch($element))
        {
            throw new Exception('type mismatch');
        }
    }
    
    protected function assertArrayType(array $items)
    {
        foreach ($items as $item) 
        {
        	$this->assertType($item);
        }
        return $items;
    }
}
?>