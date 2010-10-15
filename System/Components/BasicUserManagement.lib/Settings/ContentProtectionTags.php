<?php
/**
 * Description of Settings_ContentProtectionTags
 *
 * @author lse
 */
class Settings_ContentProtectionTags
	implements
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings
{
	public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e) {
		$tags = STagPermissions::getProtectedTags();
		$e->addClassSettings($this, 'content_handling', array(
        	'define_tags_that_prevent_unauthorized_access' => array(implode(', ', $tags), Settings::TYPE_TEXT, null, 'define_tags_that_prevent_unauthorized_access')
		));
	}
	
	public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e) {
		$data = $e->getClassSettings($this);
		if(isset($data['define_tags_that_prevent_unauthorized_access'])){
			$f = $data['define_tags_that_prevent_unauthorized_access'];
			$tags = STag::parseTagStr($f);
			STagPermissions::setProtectedTags($tags);
		}
	}
}
?>