<?php
/**
 * Description of SaveInterceptor
 *
 * @author lse
 */
class HTMLCleaner_SaveInterceptor
	extends BObject
	implements Event_Handler_WillSaveContent
{
	public function is($key){
		$v = Core::settings()->get($key);
		return !empty($v);
	}

	public function handleEventWillSaveContent(Event_WillSaveContent $e) {
		if($e->Content instanceof CPage){
			if($this->is('HTMLCleaner_Clean_HTML')){
				$p = new HTMLCleaner_Parser($e->Content->getContent());
				if($this->is('HTMLCleaner_Remove_Scripts')){
					$p->addCleaner(new HTMLCleaner_Cleaner_RemoveScripts());
				}
				if ($this->is('HTMLCleaner_Remove_StyleAttribute')){
					//remove all style atts
					$p->addCleaner(new HTMLCleaner_Cleaner_CSSStyle(array(), HTMLCleaner_Cleaner_CSSStyle::MODE_ALLOW_ONLY));
				}
				$e->Content->setContent($p->run());
			}
		}
	}
}
?>