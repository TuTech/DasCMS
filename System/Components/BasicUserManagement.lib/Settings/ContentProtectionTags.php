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
        	'define_tags_that_prevent_unauthorized_access' => array(implode(', ', $tags), Settings::TYPE_TEXT, null, 'define_tags_that_prevent_unauthorized_access')
		));
	}
	
	public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e) {
		$data = $e->getClassSettings($this);
		if(isset($data['define_tags_that_prevent_unauthorized_access'])){
			$f = $data['define_tags_that_prevent_unauthorized_access'];
			$tags = STag::parseTagStr($f);
			STagPermissions::setProtectedTags($tags);
		}
	}
}
?>