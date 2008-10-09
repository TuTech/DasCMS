<?php
class TEngine extends BTemplate 
{
    private $executionStack;
    private $closed = false;
    /**
     * constructor
     *
     * @param string $template
     * @param int $source 1 or 2
     */
    public function __construct($template, $source, array $environment = array())
    {
        //load
        $filename = sprintf(
            '%sDATA_%s_%s.php'
			,($this->source == parent::SYSTEM) ? (SPath::SYSTEM_TEMPLATES) : (SPath::TEMPLATES)
            ,$this->source
            ,$template
        );
        $this->executionStack = DFileSystem::LoadData($filename);
        //setUp()
        foreach ($this->executionStack as $object) 
        {
        	if(is_object($object) && $object instanceof ITemplateCommand)
        	{
    	        $object->setUp($environment);
        	}
        }
        
    }
    
    /**
     * execute template
     *
     * @return unknown
     */
    public function execute(array $environment = array())
    {
        if($this->closed)
        {
            throw new XTemplateException('template closed');
        }
        //run()
        $parsed = '';
        foreach ($this->executionStack as $object) 
        {
        	if(is_object($object) && $object instanceof ITemplateCommand)
        	{
    	        $parsed .= $object->run($environment);
        	}
        	else
        	{
        	    $parsed .= strval($object);
        	}
        }      
        return $parsed;
    }
    
    public function close()
    {
        if(!$this->closed)
        {
            //tearDown()
            foreach ($this->executionStack as $object) 
            {
            	if(is_object($object) && $object instanceof ITemplateCommand)
            	{
        	        $object->tearDown();
            	}
            }
            $this->closed = true;
        }
    }
    
    public function __destruct()
    {
        if(!$this->closed)
        {
            $cwd = getcwd();
            chdir(BAMBUS_CMS_ROOTDIR);
            $this->close();
            chdir($cwd);
        }
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->execute();
    }
}
?>