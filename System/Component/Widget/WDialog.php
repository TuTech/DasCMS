<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-18
 * @license GNU General Public License 3
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
    
    private $captions = array(
        1 => 'ok',
        2 => 'cancel',
        4 => 'reset'
    );
    
    public function __construct($title, $buttons)
    {       
        $this->ID = ++parent::$CurrentWidgetID;
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
        printf('org.bambuscms.wdialog.dialogs[%d] = ', $this->ID);
        printf('{"OK":%s, "Cancel":%s, "Reset":%s, "title":"%s", "sections":[',
            ($this->buttons & self::SUBMIT) ? ('"'.SLocalization::get($this->captions[self::SUBMIT]).'"') : 'null',
            ($this->buttons & self::CANCEL) ? ('"'.SLocalization::get($this->captions[self::CANCEL]).'"') : 'null',
            ($this->buttons & self::RESET) ? ('"'.SLocalization::get($this->captions[self::RESET]).'"') : 'null',
            htmlentities($this->translateTitles ? (SLocalization::get($this->title)) : $this->title, ENT_QUOTES, 'UTF-8')
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
    	        '"%s":{"type":"%s","title":"%s","value":%s}%s',
				$item[2],
				$item[1],
				$item[3],
				'"'.$item[4].'"',
				$isep
        	);
	        $isep = ',';
        }
        if($sep != '')
        {
            echo '}}';
        }
        echo ']};';
        printf('org.bambuscms.wdialog.run(%d);', $this->ID);
        echo '</script>';
    }
    
    public function run()
    {
    }
}
?>