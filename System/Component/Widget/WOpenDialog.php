<?php
class WOpenDialog extends BWidget 
{
    protected static $CurrentWidgetID = 0;
    /**
     * @var BAppController
     */
    private $editor;
    private $autoload = false;
    
    public function autoload($yn = true)
    {
        $this->autoload = ($yn == true);
    }
    
    public function __construct(BAppController $editor, $BContent = null)
    {
        $this->editor = $editor;
        if($BContent == null)
        {
            $this->autoload(true);
        }
    }
    
    /**
     * return rendered html
     *
     */
    public function __toString()
    {
        $script =
            'org.bambuscms.wopenfiledialog.setSource({'.
                '\'controller\':\''.$this->editor->getGUID().'\','.
                '\'call\':\'provideOpenDialogData\''.
            '});'.
            'org.bambuscms.wopenfiledialog.prepareLinks("'.
                SLink::link(array('edit' => '')).
             '","");';
         if($this->autoload)
         {
            $script .= 
                'org.bambuscms.wopenfiledialog.closable = false;'.
                'org.bambuscms.wopenfiledialog.show();';
         }   
         return strval(new WScript($script));
    }
    
    /**
     * process inputs etc
     *
     */
    public function run(){} 
    
    /**
     * echo html 
     */
    public function render()
    {
        echo $this->__toString();
    }
    /**
     * return ID of primary editable element or null 
     *
     * @return string|null
     */
    public function getPrimaryInputID()
    {
        return null;
    }
}
?>