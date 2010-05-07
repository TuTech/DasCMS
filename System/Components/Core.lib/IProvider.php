<?php
/**
 *
 * @author lse
 */
interface IProvider {
	public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e);
    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e);
    public function getInterface();
    public function getPurpose();
    public function getImplementor();
}
?>
