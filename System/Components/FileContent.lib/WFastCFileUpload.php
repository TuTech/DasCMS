<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2010-07-05
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WFastCFileUpload extends BWidget implements ISidebarWidget
{
	/**
	 * @var CFile
	 */

	private $targetObject = null;
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(WSidePanel::PROPERTY_EDIT)
			&& $sidepanel->getTarget() instanceof CFile //FIXME checking for cfile content but should check for AFiles controller
	    );
	}
	
	public function getName()
	{
	    return 'fast_upload';
	}
	
	public function getIcon()
	{
	    return new WIcon('transfer-upload','',WIcon::SMALL,'action');
	}
	
	public function processInputs()
	{
		if(RSent::hasValue('WFastCFileUpload-didUpload')){
			$this->targetObject->setTags(RSent::get('WFastCFileUpload-tags', CHARSET));
			if(RSent::hasValue('WFastCFileUpload-publish')){
				$this->targetObject->setPubDate(time());
			}
		}
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
	}
	
	public function __toString()
	{
		$html = '<div id="WFastCFileUpload">';
		$html .= '<form action="'.SLink::link(array('_action' => 'create')).'" id="WFastCFileUpload-form" method="post" enctype="multipart/form-data">'.
		'<label for="WFastCFileUpload-file">'.SLocalization::get('drag_a_file_to_the_field_below_to_upload_it').'</label>'.
		'<input name="CFile" type="file" onchange="$(\'WFastCFileUpload-form\').submit();" id="WFastCFileUpload-file"><br />'.
		'<input name="MAX_FILE_SIZE" type="hidden" value="1000000000">'.
		'<input name="WFastCFileUpload-didUpload" type="hidden" value="yes">'.

		'<label for="WFastCFileUpload-file">'.SLocalization::get('apply_these_tags_to_uploaded_files').'</label>'.
		'<input name="WFastCFileUpload-tags" type="text" value="'.htmlentities(RSent::get('WFastCFileUpload-tags', CHARSET), ENT_QUOTES, CHARSET).'"><br />'.

		'<label for="WFastCFileUpload-file">'.SLocalization::get('publish_file_now').'</label>'.
		'<input type="checkbox" name="WFastCFileUpload-publish" '.(RSent::hasValue('WFastCFileUpload-publish') ? ' checked="checked"' : '').' /><br />'.

		'<input type="submit" value="'.SLocalization::get('start_manual_upload').'" />'.

		'</form>';
    	$html .= '</div>';
		return $html;
	}
	
	public function associatedJSObject()
	{
	    return null;
	}
}
?>