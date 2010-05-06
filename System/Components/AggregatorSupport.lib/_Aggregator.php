<?php
abstract class _Aggregator extends _
{
    const PREDEFINED = 1;
    const DYNAMIC = 2; 
    protected $name = false;   
    protected $id = false;
    protected $attributes = array();
    
    public function getType()
    {
        //PREDEFINED || DYNAMIC
        //DYNAMIC cannot be saved
        throw new Exception('not implemented');
    }

    public function __sleep()
    {
        return $this->attributes;
    }
    
    public function __wakeup()
    {
        //allow them to be set by initDatabaseAssociation()
        $this->name = null;
        $this->id = null;
    }
    
    public function getAggregatorName()
    {
        return $this->name;
    }
       
    public function getAggregatorID()
    {
        return $this->id;
    }
    
    public function initDatabaseAssociation($name, $id)
    {
        if($this->name === null && $this->id === null)
        {
            $this->name = $name;
            $this->id = $id;
        }
        else
        {
            throw new Exception('name already set');
        }
    }
    
    /**
     * if any settings changed that needs to be saved return true
     * for PREDEFINED only
     * content will be marked to be reaggregated
     * @return bool
     */
    public function hasStateChanged()
    {
        throw new Exception('not implemented');
    }
    
    public function getContentCount()
    {
        $id = $this->getAggregatorID();
        if($id == null)
        {
            throw new XUndefinedIndexException('aggregator has no database reference');
        }
        $res = QAggregator::countAssignedContents($this->getAggregatorTable(), $this->getAggregatorID());
        list($nr) = $res->fetch();
        $res->free();
        return $nr;
    }

    /**
     * returns true if this aggregator has more to do
     * some aggregators complete in one call, others need more
     * @return bool
     */
    abstract public function aggregate();
    
    /**
     * if this is a dynamic aggregator you might want to create a temp-table 
     * the table must have 2 INT fields named contentAggregatorREL and contentREL
     * @return string
     */
    public function getAggregatorTable()
    {
        return 'relAggregatorsContents';
    } 

}
?>