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
class Controller_Application_ScriptEditor
    extends 
        _Controller_Application_Content
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog,
		Application_Interface_AppController
{
	public function  __construct() {
		if(file_exists('System/External/Bespin/BespinEmbedded.js')){
			View_UIElement_Header::relate('System/External/Bespin/', null, null, 'bespin_base');
			View_UIElement_Header::useScript('System/External/Bespin/BespinEmbedded.js');
		}
	}
	
    /**
     * required permission for class
     * @var string
     */
    protected $contentPermission = 'org.bambuscms.content.cscript';
    
    /**
     * content class
     * @var string
     */
    protected $contentClass = 'CScript';
        
    /**
     * content icon
     * @var string
     */
    protected $contentIcon = 'js';
    
    /**
	 * @var CScript
     */
    protected $target = null;
    
    const GUID = 'org.bambuscms.applications.scripteditor';

	//begin Application_Interface_AppController
	public function getTitle(){
		return 'script_editor';
	}
	public function getIcon(){
		return 'app-utilities-editor-javascript';
	}
	public function getDescription(){
		return 'script_editor';
	}
	public function getEditor(){
		return 'legacy:ScriptEditor.bap';
	}
	public function getContentObjects() {
		$this->checkPermission('view');
        return array_keys(Controller_Content::getInstance()->contentIndex($this->contentClass, true));
	}
	//end Application_Interface_AppController

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
                $this->target->setRAWContent($param['content']);
            }
            if(!empty($param['title']))
            {
                $this->target->setTitle($param['title']);
            }
            if(isset($param['subtitle']))
            {
                $this->target->setSubTitle($param['subtitle']);
            }
        }
    }
}
?>