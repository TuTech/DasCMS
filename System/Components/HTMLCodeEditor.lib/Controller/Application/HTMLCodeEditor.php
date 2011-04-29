<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-02
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class Controller_Application_HTMLCodeEditor
    extends 
        Controller_Application_WebsiteEditor
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog,
		Application_Interface_AppController
{
    const GUID = 'org.bambuscms.applications.htmlcodeeditor';

	public function  __construct() {
		if(file_exists('System/External/Bespin/BespinEmbedded.js')){
			View_UIElement_Header::relate('System/External/Bespin/', null, null, 'bespin_base');
			View_UIElement_Header::useScript('System/External/Bespin/BespinEmbedded.js');
		}
	}

    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }

	//begin Application_Interface_AppController
	public function getTitle(){
		return 'html_code_editor';
	}
	public function getIcon(){
		return 'app-editor-html-code';
	}
	public function getDescription(){
		return 'html_code_editor';
	}
	public function getEditor(){
		return 'legacy:HTMLCodeEditor.bap';
	}
	//end Application_Interface_AppController

}
?>