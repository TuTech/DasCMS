<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-18
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WDialog extends BWidget 
{
    const CANCEL = 2;
    const SUBMIT = 1;
    const RESET  = 4;
    
    private $ID;
    private $sections = array(0=>null);
    private $title = '(null)';
    private $items = array();
    private $buttons = 0;
    private $currentSection = 0;
    private $translateTitles = true;
    
    private $isMultipart = false;
    
    private $captions = array(
        1 => 'ok',
        2 => 'cancel',
        4 => 'reset'
    );
    
    public function __construct($id, $title, $buttons)
    {       
        $this->ID = $id;
        $this->setTitle($title);
        $this->setButtons($buttons);
    }

    public function setTranslateTitles($yn)
    {
        $this->translateTitles = $yn == true;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    public function setButtons($buttons)
    {
        $this->buttons = $buttons;
    }
    
    public function setButtonCaption($button, $text)
    {
        $this->captions[$button] = $text;
    }
    
    public function remember($name, $value)
    {
        $this->items[] = array(
            $this->currentSection,
            'hidden',
            $name,
            '',
            $value
        );
    }
    
    public function askText($name, $defaultValue = '', $title = null)
    {
        $this->items[] = array(
            $this->currentSection,
            'text',
            $name,
            $title,
            $defaultValue
        );
    }
    
    public function askPassword($name, $title = null)
    {
        $this->items[] = array(
            $this->currentSection,
            'password',
            $name,
            $title,
            ''
        );
    }
    public function askFile($name, $title = null)
    {
        $this->items[] = array(
            $this->currentSection,
            'file',
            $name,
            $title,
            ''
        );
        $this->isMultipart = true;
    }
    public function askConfirm($name, $checked = false, $title = null)
    {
        $this->items[] = array(
            $this->currentSection,
            'checkbox',
            $name,
            $title,
            $checked == true
        );        
    }
    public function askChoice($name, array $choices, $title = null)
    {
        $this->items[] = array(
            $this->currentSection,
            'choice',
            $name,
            $title,
            $choices
        );        
    }
    
    public function addDescription($description, $title = null)
    {
        $this->items[] = array(
            $this->currentSection,
            'description',
            '',
            $title,
            $description
        );        
    }
    
    
    public function beginSection($title = null)
    {
        $id = count($this->sections);
        $this->sections[$id] = $title;
        $this->currentSection = $id;
    }
    public function endSection($title = null)
    {
        $this->currentSection = 0;
    }
    
    
    
    
    /**
     * get render() output as string
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        $this->render();
        return ob_get_clean();
    }
    
    public function render()
    {
        echo '<script type="text/javascript">';
        printf('org.bambuscms.wdialog.dialogs.%s = ', $this->ID);
        printf('{"OK":%s, "Cancel":%s, "Reset":%s, "title":"%s", "isMultipart":%d, "sections":[',
            ($this->buttons & self::SUBMIT) ? ('"'.SLocalization::get($this->captions[self::SUBMIT]).'"') : 'null',
            ($this->buttons & self::CANCEL) ? ('"'.SLocalization::get($this->captions[self::CANCEL]).'"') : 'null',
            ($this->buttons & self::RESET) ? ('"'.SLocalization::get($this->captions[self::RESET]).'"') : 'null',
            $this->translateTitles ? (SLocalization::get($this->title)) : htmlentities($this->title, ENT_QUOTES, 'UTF-8'),
            $this->isMultipart ? '1' : '0'
        );
        $csect = '';
        $sep = '';
        $isep = '';
        foreach ($this->items as $item) 
        {
        	if($csect !== $item[0])
        	{
        	    $csect = $item[0];
        	    printf(
        	        '%s{"title":%s,"items":{'
					,$sep
        	        ,($this->sections[$csect] != null) ? '"'.$this->sections[$csect].'"': 'null'
				);
        	    $sep = ',';
        	    $isep = '';
        	}
        	printf(
        	    //'$name:{type:$type, title:$title, value:$value<-- if not choice}'
    	        '%s"%s":{"type":"%s","title":"%s","value":%s}',
    	        $isep,
				$item[2],
				$item[1],
				($this->translateTitles) ? (SLocalization::get($item[3])) : $item[3],
				'"'.$item[4].'"'
        	);
	        $isep = ',';
        }
        if($sep != '')
        {
            echo '}}';
        }
        echo ']};';
        //printf('org.bambuscms.wdialog.run("%s");', $this->ID);
        echo '</script>';
    }
    
    public static function openCommand($dialog)
    {
        return sprintf('org.bambuscms.wdialog.run("%s");', $dialog);
    }
    
    public function run()
    {
    }
}
?>