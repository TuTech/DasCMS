<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-09-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_Introduction extends _View_UIElement 
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
        $html = '<div class="View_UIElement_Introduction">';
        if($this->icon != null)
        {
            $html .= new View_UIElement_Icon($this->icon,'','medium');
        }
        if($this->title != null)
        {
            $html .= sprintf("<h2>%s</h2>\n", String::htmlEncode($this->title));
        }
        if($this->description != null)
        {
            $html .= sprintf("<p>%s</p>\n", String::htmlEncode($this->description));
        }
        $html .= '</div>';
        return $html;
    }
}

?>