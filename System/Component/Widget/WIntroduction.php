<?php
class WIntroduction extends BWidget 
{
    private $title;
    private $description;
    private $icon;
    public function __construct($title = null, $description = null, $icon = null)
    {
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setIcon($icon);
    }
    
    public function setTitle($title, $translate = true)
    {
        $this->title = $translate ? (SLocalization::get($title)) : $title;
    }

    public function setDescription($description, $translate = true)
    {
        $this->description = $translate ? (SLocalization::get($description)) : $description;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function render()
    {
        echo $this->__toString();
    }
    
    public function __toString()
    {
        $html = '<div class="WIntroduction">';
        if($this->icon != null)
        {
            $html .= new WIcon($this->icon,'','medium');
        }
        if($this->title != null)
        {
            $html .= sprintf("<h2>%s</h2>\n", htmlentities($this->title, ENT_QUOTES, 'utf-8'));
        }
        if($this->description != null)
        {
            $html .= sprintf("<p>%s</p>\n", htmlentities($this->description, ENT_QUOTES, 'utf-8'));
        }
        $html .= '</div>';
        return $html;
    }
}

?>