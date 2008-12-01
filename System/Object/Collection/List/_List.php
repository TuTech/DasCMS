<?php
abstract class _Collection_List extends _Collection 
{
    protected $items = array();
    protected $iteration;
    
    protected function __construct(array $items = array())
    {
        $this->items = $items;
    }
    
    public function toArray()
    {
        return array_keys($this->items);
    }
        
    public function add($element)
    {
        $this->items[] = $element;
    }
    
    public function remove($element)
    {
        $pos = array_search($element, $this->items);
        if($pos !== false)
        {
            unset($this->items[$pos]);
        }
    }

    public function contains($element)
    {
        return in_array($element, $this->items);
    }
    
    public function length()
    {
        return count($this->items);
    }
    
    public function startIteration()
    {
        $this->iteration = 0;
    }
    
    public function next()
    {
        $this->iteration++;
    }
       
    public function valid()
    {
        return $this->iteration < count($this->items);
    }
    
    public function get()
    {
        return $this->items[$this->iteration];
    }
}
?>