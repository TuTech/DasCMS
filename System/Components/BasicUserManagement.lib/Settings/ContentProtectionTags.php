<?php
/**
 * Description of Settings_ContentProtectionTags
 *
 * @author lse
 */
class Settings_ContentProtectionTags extends BObject
	implements
        HRequestingClassSettingsEventHandler,
        HUpdateClassSettingsEventHandler
{
	public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e) {
		$tags = STagPermissions::getProtectedTags();
		$e->addClassSettings($this, 'permissions', array(
        	'tags_to_prevent_unauthorized_access' => array(implode(', ', $tags), Settings::TYPE_TEXT, null, 'tags_to_prevent_unauthorized_access')
		));
	}
	
	public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e) {
		$data = $e->getClassSettings($this);
		if(isset($data['tags_to_prevent_unauthorized_access'])){
			$f = $data['tags_to_prevent_unauthorized_access'];
			$tags = STag::parseTagStr($f);
			STagPermissions::setProtectedTags($tags);
		}
	}
}
?>