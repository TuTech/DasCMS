<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-09
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
class TCompiler extends BTemplate
{
    
    /**
     * @var DOMDocument
     */
    private $template;
    private $templateName;
    private $source;
    /**
     * constructor
     *
     * @param string $template
     * @param int $source 1 or 2
     */
    public function __construct($template, $source)
    {
        $this->template = new DOMDocument('1.0', CHARSET);
        $this->template->strictErrorChecking = true;
        $this->source = ($source == parent::SYSTEM) ? (parent::SYSTEM) : (parent::CONTENT);
        $this->templateName = $template;
        $path = ($source == parent::SYSTEM) ? (Core::PATH_SYSTEM_TEMPLATES) : (Core::PATH_TEMPLATES);
        $path = sprintf('%s%s', $path, $template);
        SErrorAndExceptionHandler::muteErrors();
        if(!@$this->template->load($path))
        {
            
            $err = error_get_last();
            throw new XArgumentException($err['message'], $err['type']);
        }
        SErrorAndExceptionHandler::reportErrors();
        //analyse template 
        $this->analyze($this->template->documentElement);
        //our data in now in the inherited var $parsed
    }
    
    public function save()
    {
        $filename = sprintf(
            '%sDATA_%s_%s.php'
			,($this->source == parent::SYSTEM) ? (Core::PATH_SYSTEM_TEMPLATES) : (Core::PATH_TEMPLATES)
            ,$this->source
            ,$this->templateName
        );
        Core::FileSystem()->storeDataEncoded($filename, $this->parsed);
    }
}
?>