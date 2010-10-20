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
        ISupportsOpenDialog  
{
    const GUID = 'org.bambuscms.applications.htmlcodeeditor';

	public function  __construct() {
		if(file_exists('System/External/Bespin/BespinEmbedded.js')){
			View_UIElement_Header::relate('System/External/Bespin/', null, null, 'bespin_base');
			View_UIElement_Header::useScript('System/External/Bespin/BespinEmbedded.js');
		}
	}
}
?>