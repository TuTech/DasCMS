<?php
class WOpenFileDialog extends BWidget 
{
    protected static $CurrentWidgetID = 0;
    
    private $categories = array();
    private $items = array();
    
    public function registerCategory($category)
    {
        $this->categories[$category] = SLocalization::get($category);
    }
    
    public function addItem($category, $title, $link, $icon, $description)
    {
        $this->items[$title.$link] = array($category, $title, $link, $icon, $description);
    }
    
    /**
     * return rendered html
     *
     */
    public function __toString()
    {
        natcasesort($this->categories);
        $sort = array_keys($this->items);
        natcasesort($sort);
        $out = "\n<div id=\"OFD_Definition\">\n\t<span id=\"OFD_Categories\">\n";
        foreach ($this->categories as $cat) 
        {
        	$out .= "\t\t<span>".htmlentities($cat, ENT_QUOTES, 'UTF-8')."</span>\n";
        }
        $out .= "\t</span>\n\t<span id=\"OFD_Items\">\n\t";
    
        //openFileDialog files
        foreach($sort as $item)
        {
            $out .= sprintf(
                "\t".'<a href="%s">' ."\n\t\t\t".
                    '<span title="title">%s</span>' ."\n\t\t\t".
                    '<span title="icon">%s</span>' ."\n\t\t\t".
                    '<span title="description">%s</span>' ."\n\t\t\t".
                    '<span title="category">%s</span>' ."\n\t\t".
                "</a>\n\t"
                ,htmlentities($this->items[$item][2], ENT_QUOTES, 'UTF-8')
                ,htmlentities($this->items[$item][1], ENT_QUOTES, 'UTF-8')
                ,WIcon::pathFor($this->items[$item][3], 'mimetype',WIcon::MEDIUM)
                ,htmlentities($this->items[$item][4], ENT_QUOTES, 'UTF-8')
                ,$this->categories[$this->items[$item][0]]
            );
        }
        $out .=  "</span>\n</div>\n";
        $out .= '<script language="JavaScript" type="text/javascript">'."\n\t".
            'var OBJ_ofd;'."\n\t".
            'OBJ_ofd = new CLASS_OpenFileDialog();'."\n\t".
            'OBJ_ofd.self = "OBJ_ofd";'."\n\t".
            'OBJ_ofd.openIcon = "'.WIcon::pathFor('open').'";'."\n\t".
            'OBJ_ofd.openTranslation = "'.SLocalization::get('open').'";'."\n\t".
            'OBJ_ofd.closeIcon = "'.WIcon::pathFor('delete').'";'."\n\t".
            'OBJ_ofd.statusText = "";'."\n\t".
            'OBJ_ofd.statusAnimation = "'.WIcon::pathFor('loading', 'animation', WIcon::EXTRA_SMALL).'";'."\n".
        '</script>'."\n";
        return $out;
    }
    
    /**
     * process inputs etc
     *
     */
    public function run(){} 
    
    /**
     * echo html 
     */
    public function render()
    {
        echo $this->__toString();
    }
    /**
     * return ID of primary editable element or null 
     *
     * @return string|null
     */
    public function getPrimaryInputID()
    {
        return null;
    }
}
?>