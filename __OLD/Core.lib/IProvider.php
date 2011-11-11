<?php
/**
 *
 * @author lse
 */
interface IProvider {
	public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e);
    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e);
    public function getInterface();
    public function getPurpose();
    public function getImplementor();
}
?>
