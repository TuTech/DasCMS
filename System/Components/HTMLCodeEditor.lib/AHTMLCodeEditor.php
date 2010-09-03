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
class AHTMLCodeEditor
    extends 
        AWebsiteEditor
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog  
{
    const GUID = 'org.bambuscms.applications.htmlcodeeditor';

	public function  __construct() {
		if(file_exists('System/External/Bespin/BespinEmbedded.js')){
			WHeader::relate('System/External/Bespin/', null, null, 'bespin_base');
			WHeader::useScript('System/External/Bespin/BespinEmbedded.js');
		}
	}
}
?>