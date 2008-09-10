<?php
abstract class BProvider
{
    //serves one purpose

    //has assigned interface

    //uses one class implementing the interface to provide services

    /**
     * providing access to this interface 
     * @var string
     */
    protected $Interface;
    
    /**
     * interface implementation to handle the requests
     * @var string
     */
    protected $Implementor = null;
    
    /**
     * interface implementation to handle the requests
     * @var string
     */
    protected $HasImplementor = null;
    
    /**
     * getter for $Interface
     * @return string
     */
    public function getInterface()
    {
        return $this->Interface;
    }
    
    /**
     * getter for $Implementation
     *
     * @return string
     * @throws XUndefinedException
     */
    public function getImplementor()
    {
        if($this->HasImplementor === null)
        {
            $impl = LConfiguration::get(get_class($this));
            $this->HasImplementor = !empty($impl) && class_exists($impl, true);
            if($this->HasImplementor)
            {
                 $this->Implementor = $impl;
            }
        }
        if(!$this->HasImplementor)
        {
            throw new XUndefinedException('provider has not been set up');
        }
        return $this->Implementation;
    }
}
?>