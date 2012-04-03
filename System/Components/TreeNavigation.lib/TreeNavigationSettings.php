<?php
class TreeNavigationSettings
    implements
        Event_Handler_UpdateClassSettings,
        Event_Handler_RequestingClassSettings
{
    public function handleEventRequestingClassSettings(Event_RequestingClassSettings $e)
    {
        $e->addClassSettings($this, 'navigation', array(
        	'use_css_navigation' => array(
						Core::Settings()->get('navigation.render_full'), 
						Settings::TYPE_CHECKBOX, 
						null, 
						'use_css_navigation'
					)
        ));
        DSQL::getInstance()->handleEventRequestingClassSettings($e);
    }
    
    public function handleEventUpdateClassSettings(Event_UpdateClassSettings $e)
    {
      $data = $e->getClassSettings($this);
      if(isset($data['use_css_navigation']))
      {
          Core::Settings()->set('navigation.render_full', $data['use_css_navigation']);
      }
    }
}
?>