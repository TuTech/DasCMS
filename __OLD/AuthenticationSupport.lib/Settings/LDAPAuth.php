<?php
/**
 * Description of Settings_LDAPAuth
 *
 * @author lse
 */
class Settings_LDAPAuth
	implements
        Event_Handler_RequestingClassSettings,
        Event_Handler_UpdateClassSettings
{
	public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e) {
		$cfg = Core::Settings();
		$e->addClassSettings($this, 'ldap_login', array(  	
        	'server' 	=> array($cfg->get('system.ldap.server'), 	Settings::TYPE_TEXT,	 null, 'ldap_server'),
        	'user' 		=> array($cfg->get('system.ldap.user'), 	Settings::TYPE_TEXT,	 null, 'ldap_user'),
        	'password' 	=> array($cfg->get('system.ldap.password'),	Settings::TYPE_PASSWORD, null, 'ldap_password'),
        	'domain' 	=> array($cfg->get('system.ldap.domain'), 	Settings::TYPE_TEXT,	 null, 'ldap_domain')
		));
	}
	
	public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e) {
		$data = $e->getClassSettings($this);
		$cfg = Core::Settings();
		foreach(array('server', 'user', 'password', 'domain') as $key){
			if(isset($data[$key])){
				$cfg->set('system.ldap.'.$key, $data[$key]);
			}
		}
	}
}
?>