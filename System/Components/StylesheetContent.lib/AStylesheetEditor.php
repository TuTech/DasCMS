<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-29
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class AStylesheetEditor
    extends 
        BAContentAppController
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog
{
	public function  __construct() {
		if(file_exists('System/External/Bespin/prebuilt/BespinEmbedded.js')){
			WHeader::relate('System/External/Bespin/', null, null, 'bespin_base');
			WHeader::useScript('System/External/Bespin/prebuilt/BespinEmbedded.js');
		}
	}
	
	/**
     * required permission for class
     * @var string
     */
    protected $contentPermission = 'org.bambuscms.content.cstylesheet';
    
    /**
     * content class
     * @var string
     */
    protected $contentClass = 'CStylesheet';
    
    /**
     * content icon
     * @var string
     */
    protected $contentIcon = 'css';
    
    /**
	 * @var CStylesheet
     */
    protected $target = null;
    
    const GUID = 'org.bambuscms.applications.stylesheeteditor';
    
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function save(array $param)
    {
        $this->checkPermission('change');
        if($this->target != null)
        {
            if(isset($param['content']))
            {
                $this->target->RAWContent = $param['content'];
            }
            if(!empty($param['title']))
            {
                $this->target->Title = $param['title'];
            }
            if(isset($param['subtitle']))
            {
                $this->target->SubTitle = $param['subtitle'];
            }
        }
    }
}
?>