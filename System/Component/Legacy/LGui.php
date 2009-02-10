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
        "<textarea  spellcheck=\"%s\" ".
        "name=\"content\" class=\"visibleEditor\" wrap=\"on\" ".
        "id=\"org_bambuscms_app_document_editorElementId\" cols=\"60\" rows=\"15\">"
            , $spellcheck ? 'true' : 'false'
        );
        $out .= htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        $out .= "</textarea>\n";
        $resize = new WScript(
            '(function(){
            	var h = -190;
                if($(org.bambuscms.app.document.editorElementId).offsetTop)
                {
            		h = function(){return ($(org.bambuscms.app.document.editorElementId).offsetTop+5)*-1;};
            	}
                org.bambuscms.display.setAutosize(org.bambuscms.app.document.editorElementId,0,h);
            })();');
        $out .= $resize->__toString();
        return $out;
    }
    
    public static function verticalSpace()
    {
        return "<br />";
    }
}
?>