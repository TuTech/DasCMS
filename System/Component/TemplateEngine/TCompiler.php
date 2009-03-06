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
        $this->template = new DOMDocument('1.0', 'utf-8');
        $this->source = ($source == parent::SYSTEM) ? (parent::SYSTEM) : (parent::CONTENT);
        $this->templateName = $template;
        $path = ($source == parent::SYSTEM) ? (SPath::SYSTEM_TEMPLATES) : (SPath::TEMPLATES);
        $path = sprintf('%s%s', $path, $template);
        if(!@$this->template->load($path))
        {
            $err = error_get_last();
            throw new XArgumentException($err['message'], $err['type']);
        }
        //analyse template 
        $this->analyze($this->template->documentElement);
        //our data in now in the inherited var $parsed
    }
    
    public function save()
    {
        $filename = sprintf(
            '%sDATA_%s_%s.php'
			,($this->source == parent::SYSTEM) ? (SPath::SYSTEM_TEMPLATES) : (SPath::TEMPLATES)
            ,$this->source
            ,$this->templateName
        );
        DFileSystem::SaveData($filename, $this->parsed);
    }
}
?>