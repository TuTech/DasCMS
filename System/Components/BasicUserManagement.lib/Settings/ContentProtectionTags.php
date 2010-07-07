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
        	'tags_to_prevent_unauthorized_access' => array(implode(', ', $tags), Settings::TYPE_TEXT, null, 'protection_tags')
		));
	}
	
	public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e) {
		$data = $e->getClassSettings($this);
		if(isset($data['protection_tags'])){
			$f = $data['protection_tags'];
			$tags = STag::parseTagStr($f);
			STagPermissions::setProtectedTags($tags);
		}
	}
}
?>