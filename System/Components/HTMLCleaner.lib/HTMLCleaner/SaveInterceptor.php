<?php
/**
 * Description of SaveInterceptor
 *
 * @author lse
 */
class HTMLCleaner_SaveInterceptor
	extends BObject
	implements HWillSaveContentEventHandler
{
	public function is($key){
		$v = Core::settings()->get($key);
		return !empty($v);
	}

	public function HandleWillSaveContentEvent(EWillSaveContentEvent $e) {
		if($e->Content instanceof CPage){
			if($this->is('HTMLCleaner_Clean_HTML')){
				$p = new HTMLCleaner_Parser($e->Content);
				if($this->is('HTMLCleaner_Remove_Scripts')){
					$p->addCleaner(new HTMLCleaner_Cleaner_RemoveScripts());
				}
				$e->Content = $p->run();
			}
		}
	}
}
?>