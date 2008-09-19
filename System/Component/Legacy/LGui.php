<?php
class LGui extends BLegacy  
{
    private static function createElement($tag, $value = '', $attributes = false)
    {
        $attributeList = '';
        $element = '';
        if(is_array($attributes))
        {
            foreach($attributes as $title => $text)
            {
                $attributeList .= sprintf(' %s="%s"', $title, str_replace('"', '&quot;',$text));
            }
        }
        if($value === false)
        {
            //self-closing tag//
            $element = sprintf("<%s%s />\n", $tag, $attributeList);
        }
        else
        {
            //tag with value//
            $element = sprintf("<%s%s>%s</%s>\n", $tag, $attributeList, $value, $tag);
        }
        return $element;
    }

    //editor wrapper//

    public static function beginEditorWrapper()
    {
        return "<div id=\"editorwrapper\">\n";
    }

    public static function endEditorWrapper()
    {
        return "</div>\n";
    }

    //search input//
    public static function search($function)//used: Application
    {
        $image ='';
        $isSafari = false;
        $input = self::createElement('input', false, array(
        'type'      => ($isSafari) ? "search" : "text",
        'id'        => ($isSafari) ? "searchsearchField" : "textsearchField",
        'name'      => "searchFilter",
        'onkeyup'   => $function."(this.value)"
                    ));
        return self::createElement('div', $input.$image, array('id' => "searchFieldBox"));
    }

    public static function beginMultipartForm($_get_values = array(), $id = '')
    {
        if(!is_array($_get_values))$_get_values = array();
        return sprintf(
            "<form enctype=\"multipart/form-data\" action=\"%s\"%s method=\"post\">\n", 
            SLink::link($_get_values), 
            (empty($id)) 
                ? '' 
                : (sprintf(' id="%s"', $id).(($id == 'documentform') ? ' name="documentform"' : '')));
    }

    public static function endMultipartForm()
    {
        return "</form>\n";
    }

    public static function beginTaskBar()
    {
        return "<div id=\"taskbar\" class=\"nohotkeys\">\n";
    }

    public static function taskSpacer()
    {
        return "<div class=\"taskspacer\"></div>\n";
    }

    private static $hotKeys = array();
    public static function taskButton($Action, $IsJSAction, $icon, $Caption, $hotkey = '')
    {
        $hotkeyinfo = '';
        if (!empty($hotkey))
        {
            $keyCode = ord($hotkey);
            self::$hotKeys[$hotkey] = array(($IsJSAction) ? $Action : str_replace('&amp;', '&', $Action), true);
            if($keyCode >= 65 && $keyCode<=90) // A-Z
            {
                $hotkey = '^'.$hotkey;
            }
            $hotkeyinfo = self::createElement('span',$hotkey, array('class' => "hotkeyinfo"));
        }
        $image = new WIcon($icon, $Caption,WIcon::SMALL);

        $Action = "javascript:".$Action;
        $a = self::createElement('a', $hotkeyinfo.$image, array('href' => $Action, 'title' => $Caption.''));
        $e = self::createElement('div', $a, array('class' => "taskbutton"));
        return $e;
    }

    public static function endTaskBar()
    {
        return "<br class=\"clear\" /></div>\n";
    }

    public static function beginForm($_get_values = array(), $id = '')
    {
        if($id == 'documentform')
        {
            $id = 'documentform" name="documentform';
        }
        $id = (empty($id)) ? '' : ' id="'.$id.'"';
        if(!is_array($_get_values))$_get_values = array();
        $action = SLink::link($_get_values);
        return "<form action=\"".$action."\" method=\"post\"".$id.">\n";
    }

    public static function endForm()
    {
        return "</form>\n";
    }

    public static function hiddenInput($name, $value, $id = '')
    {
        if(!empty($id)){
            $id = 'id="'.$id.'" ';
        }
        return "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\" ".$id."/>\n";
    }

    public static function beginApplication()
    {
        return "<div id=\"BambusContentArea\">\n<div id=\"BambusApplication\">\n";
    }

    public static function endApplication()
    {
        return "</div>\n</div>\n";
    }

    public static function beginEditor()
    {
        return "<div id=\"editor\">\n";
    }

    public static function endEditor()
    {
        return "</div>\n";
    }

    public static function editorTextarea($content, $spellcheck = false)
    {
        $out = "<input type=\"hidden\" name=\"action\" value=\"save\" />\n";
        $out .= sprintf(
        "<textarea  spellcheck=\"%s\" onkeyup=\"curpos();actv();\" ".
        "onmouseup=\"curpos();actv();\" onfocus=\"curpos();actv();\" ".
        "name=\"content\" class=\"visibleEditor\" wrap=\"on\" ".
        "id=\"editorianid\" cols=\"60\" rows=\"15\">"
            , $spellcheck ? 'true' : 'false'
        );
        $out .= htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        $out .= "</textarea>\n";
        return $out;
    }
    
    public static function verticalSpace()
    {
        return "<br />";
    }

    public static function beginTable($id = '',$class = 'borderedtable full')
    {
        $table = "<table cellspacing=\"0\"";
        if(!empty($id))
        {
            $table .= " id=\"".$id."\"";
        }
        if(!empty($class))
        {
            $table .= " class=\"".$class."\"";
        }
        $table .= ">\n";
        return $table;
    }

    public static function beginTableRow($class = '', $tdclass = "")
    {
        if(!empty($class))
        {
            $class = ' class="'.$class.'"';
        }
        if(!empty($tdclass))
        {
            $tdclass = ' class="'.$tdclass.'"';
        }
        return "<tr valign=\"top\"".$class.">\n<td".$tdclass.">\n";
    }

    public static function endTableRow()
    {
        return "</td>\n</tr>\n";
    }

    public static function endTable()
    {
        return "</table>\n";
    }

    public static function tableHeader($cells = array())
    {
        $header = '';
        if(is_array($cells) && count($cells) > 0)
        {
            $classes = array_keys($cells);
            $header = "<tr>";
            foreach($classes as $cellid)
            {
                $header .= "<th";
                if(!is_numeric($cellid))
                {
                    $header .= " class=\"".trim($cellid)."\"";
                }
                $header .= ">".$cells[$cellid]."</th>";
            }
            $header .= "</tr>\n";
        }
        return $header;
    }
}
?>