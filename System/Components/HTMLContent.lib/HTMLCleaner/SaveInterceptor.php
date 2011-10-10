<?php
/**
 * Description of SaveInterceptor
 *
 * @author lse
 */
class HTMLCleaner_SaveInterceptor
	implements Event_Handler_WillSaveContent
{
	public function is($key){
		$v = Core::Settings()->get($key);
		return !empty($v);
	}

	public function handleEventWillSaveContent(Event_WillSaveContent $e) {
		if($e->getContent() instanceof CPage){
			if($this->is('HTMLCleaner_Clean_HTML')){
				$p = new HTMLCleaner_Parser($e->getContent()->getContent());
				$p->addCleaner(new HTMLCleaner_Cleaner_MSOfficeComments());
				if($this->is('HTMLCleaner_Remove_Scripts')){
					$p->addCleaner(new HTMLCleaner_Cleaner_RemoveScripts());
				}
				if ($this->is('HTMLCleaner_Remove_StyleAttribute')){
					//remove all style atts
					$p->addCleaner(new HTMLCleaner_Cleaner_CSSStyle(array(), HTMLCleaner_Cleaner_CSSStyle::MODE_ALLOW_ONLY));
				}
				$e->getContent()->setContent($p->run());
			}
		}
	}
}
?>