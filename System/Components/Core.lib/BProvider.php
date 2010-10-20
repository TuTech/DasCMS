<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-10
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage BaseClasses
 */
abstract class BProvider 
{
    public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
    {
        $class = get_class($this);
        $implementors = Core::getClassesWithInterface($this->getInterface());
        $ipls = array();
        foreach ($implementors as $impl)
        {
            $ipls[SLocalization::get(constant($impl.'::NAME'))] = $impl;
        }
        $data = array();
        $data['implementation'] = array(Core::Settings()->get($class), Settings::TYPE_SELECT, $ipls, $class);
        $e->addClassSettings($this, $this->getPurpose(), $data);
    }
    
    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
    {
        $class = get_class($this);
        $data = $e->getClassSettings($this);
        
        if(!empty($data['implementation']))
        {
            Core::Settings()->set($class, $data['implementation']);
        }
    }
    
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
     * purpose in config section
     * @var string
     */
    protected $Purpose = 'provider';
    
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
     * getter for $Interface
     * @return string
     */
    public function getPurpose()
    {
        return $this->Purpose;
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
            $impl = Core::Settings()->get(get_class($this));
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
        return $this->Implementor;
    }
}
?>