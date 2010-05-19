<?php
/**
 * Description of CPageWYSIWYG
 *
 * @author lse
 */
class Settings_CPageWYSIWYG
	extends BObject
    implements
        HUpdateClassSettingsEventHandler,
        HRequestingClassSettingsEventHandler
{
	private static $defaults = array(
		1 => "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect",
		2 => "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		3 => "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		4 => "styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking"
	);

    public function HandleRequestingClassSettingsEvent(ERequestingClassSettingsEvent $e)
    {
		$this->initSettings();
        //db_engine + whatever DSQL gives us
         $fields = array(
        	'enable_wysiwyg' => array(Core::settings()->get('AWebsiteEditor_WYSIWYG'), Settings::TYPE_CHECKBOX, null, 'enable_wysiwyg'),
        	'reset_controls' => array('', Settings::TYPE_CHECKBOX, null, 'reset_controls')
        );

		for($i = 1; $i < 5; $i++){
			$fields['tinyMCE_controls_in_row'.$i] = array(
				Core::settings()->get('AWebsiteEditor_WYSIWYG_row'.$i),
				Settings::TYPE_TEXT,
				null,
				'tinyMCE_controls_in_row'.$i
			);
		}
		$e->addClassSettings($this, 'website_editor', $fields);
    }

    public function HandleUpdateClassSettingsEvent(EUpdateClassSettingsEvent $e)
    {
        $data = $e->getClassSettings($this);
		if(isset($data['enable_wysiwyg']))
		{
			Core::settings()->set('AWebsiteEditor_WYSIWYG', !empty ($data['enable_wysiwyg']) ? '1' : '');
		}
		if(!empty ($data['reset_controls'])){
			$this->initSettings(true);
		}
		else{
			for($i = 1; $i < 5; $i++){
				if(isset($data['tinyMCE_controls_in_row'.$i])){
					$value = $data['tinyMCE_controls_in_row'.$i];
					if(preg_match('/^[a-zA-Z0-9,|]*$/mu', $value)){
						Core::settings()->set('AWebsiteEditor_WYSIWYG_row'.$i, $value);
					}
				}
			}
		}
    }

	private function initSettings($reset = false){
		for($i = 1; $i < 5; $i++){
			$v = Core::settings()->getOrDefault('AWebsiteEditor_WYSIWYG_row'.$i, null);
			if($v === null || $reset){
				Core::settings()->set('AWebsiteEditor_WYSIWYG_row'.$i, self::$defaults[$i]);
			}
		}
	}
}
?>