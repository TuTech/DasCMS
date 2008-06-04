<?php 
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 12.06.2006
 * @license GNU General Public License 3
 */
class Gui extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'Gui';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!self::$initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	self::$initializedInstance = true;
			$this->Linker = Linker::alloc();

			$this->Linker->init();
    	}
    }
	//end IShareable

    var $get,$post,$session;
    var $notifierObjects = array();
    
    ////////////////////
    //init enviornment//
    ////////////////////
    
    function __construct()
    {
        parent::Bambus();
    }
    
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
	function createElement($tag, $value = '', $attributes = false)
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

	
	//object title//
	
	function objectTitle($name, $descriptionOrDescriptions = false)
	{
		$p = '';
		if($descriptionOrDescriptions)
		{
			$descriptions = $this->isAnArray($descriptionOrDescriptions);
			foreach($descriptions as $description)
			{
				$p .= $this->createElement('p', $description);
			}
		}
		$h2  = $this->createElement('h3', $name);
		return $this->createElement('div', $h2.$p, array('id' => "objectInformation"));
	}
	
	//small notifier//
	
	function applicationNotifier()
	{
		return $this->createElement('span', '', array('id' => "js_message", 'class' => "small_message"));
	}
	
	//line number container//
	
 	function lineNumberDisplay()
	{
		return $this->createElement('div', '&nbsp;', array('id' => "linenr"));
	}
	
	//editor actions//
	
	function beginDisabledWYSIWYGActions()
	{
		return "<div id=\"disabledWYSIWYGActions\">\n";
	}
	
	function endDisabledWYSIWYGActions()
	{
		return "</div>\n";
	}
	
	function beginEnabledWYSIWYGActions()
	{
		return "<div id=\"enabledWYSIWYGActions\">\n";
	}
	
	function endEnabledWYSIWYGActions()
	{
		return "</div>\n";
	}
	
	//editor wrapper//
	
 	function beginEditorWrapper()
	{
		return "<div id=\"editorwrapper\">\n";
	}
	
	function endEditorWrapper()
	{
		return "</div>\n";//<br class=\"clear\" />
	}
	
	//search input//
	
	function search($function)
	{
		$image ='';
		$isSafari = false;
		$input = $this->createElement('input', false, array(
						'type' 		=> ($isSafari) ? "search" : "text",
						'id'		=> ($isSafari) ? "searchsearchField" : "textsearchField",
						'name' 		=> "searchFilter",
						'onkeyup' 	=> $function."(this.value)"
					));
		return $this->createElement('div', $input.$image, array('id' => "searchFieldBox"));
	}
		
	function applicationTitle($ApplicationTitle, $EditingObject = '')
	{
		if(empty($EditingObject))
		{
			$html = sprintf("<h2>%s %s</h2>", BAMBUS_APPLICATION_ICON, htmlentities($ApplicationTitle));
		}
		else
		{
			$html = sprintf("<h2>%s %s: \"%s\"</h2>", BAMBUS_APPLICATION_ICON, htmlentities($ApplicationTitle), htmlentities($EditingObject));
		} 
		return '';//$html;
	}
	//tab bar with big icons//
	
	function iconPath($name, $title = '', $type = false, $size = 'small')
	{
		//lookup tango icon structure
		//- seperated specifyer
		$parts = explode('-', $name);
		if(!$type)
		{
			//specifyer in name
			$type = array_shift($parts);
		}
		$path = parent::pathTo('systemIcon').'tango/'
			.strtolower($size).'/'.strtolower($type).'s/';
		
		if(ctype_alpha($size) && ctype_alpha($type) && is_dir($path))
		{
			//valid path
			$suffix = '.png';
			while(count($parts) > 0)
			{
				//try to find most specific icon
				$file = $path.implode('-',$parts).$suffix;
				if(file_exists($file))
				{
					return $file;
				}
				array_pop($parts);
			}
		}
		//lookup old bambus icon structure
		$path = parent::pathTo('systemIcon');
		switch(strtolower($size))
		{
			case 'large':
			case 48:
				$path .= '48x48/';
				$len = 48;
				break;
			case 'medium':
			case 32:
				$path .= '32x32/';
				$len = 32;
				break;
			case 'extra-small':
			case 16:
				$path .= '16x16/';
				$len = 16;
				break;
			case 'small':
			case 22:
			default:
				$path .= '22x22/';
				$len = 22;
				break;
		}
		if($type == false)
		{
			//type is part of image name
			$parts = explode('-', $name);
			$type = $parts[0];
			unset($parts[0]);
			$name = implode('-', $parts);
		}
		//actionS deviceS etc...
		$type = substr($type, -1) == 's' ? $type : $type.'s';
		$suffix =  '.png';
		$discarded = '.gif';
		//change suffix to match browser snafu
		//$name = substr($name, 0, -4);
		if(!file_exists($path.$type.'/'.$name.$suffix) && file_exists($path.$type.'/'.$name.$discarded))
			$suffix = $discarded;
		$path .= $type.'/'.$name.$suffix;
        return  $path;
 	}	
	function icon($name, $title, $type = false, $size = 'small', $color = true)
	{
		$tangoIcon = false;
		//lookup tango icon structure
		//- seperated specifyer
		$parts = explode('-', $name);
		$ttype = (!$type) ? array_shift($parts) : $type;
		$path = parent::pathTo('systemIcon').'tango/'
			.strtolower($size).'/'.strtolower($ttype).'s/';
		
		if(ctype_alpha($size) && ctype_alpha($ttype) && is_dir($path))
		{
			//valid path
			$suffix = '.png';
			while(count($parts) > 0)
			{
				//try to find most specific icon
				$file = $path.implode('-',$parts).$suffix;
				if(file_exists($file))
				{
					$tangoIcon = $file;
					break;
				}
				array_pop($parts);
			}
		}		
		$path = parent::pathTo('systemIcon');
		switch(strtolower($size))
		{
			case 'large':
			case 48:
				$path .= '48x48/';
				$len = 48;
				break;
			case 'medium':
			case 32:
				$path .= '32x32/';
				$len = 32;
				break;
			case 'extra-small':
			case 16:
				$path .= '16x16/';
				$len = 16;
				break;
			case 'small':
			case 22:
			default:
				$path .= '22x22/';
				$len = 22;
				break;
		}
		if(!$tangoIcon)
		{
			//TODO: utilize function above
			

			if($type == false)
			{
				//type is part of image name
				$parts = explode('-', $name);
				$type = $parts[0];
				unset($parts[0]);
				$name = implode('-', $parts);
			}
			//actionS deviceS etc...
			$type = substr($type, -1) == 's' ? $type : $type.'s';
			$suffix =  '.png';
			$discarded = '.gif';
			//change suffix to match browser snafu
			//$name = substr($name, 0, -4);
			if(!file_exists($path.$type.'/'.$name.$suffix) && file_exists($path.$type.'/'.$name.$discarded))
				$suffix = $discarded;
			$path .= $type.'/'.$name.($color ? '' : '-gray').$suffix;
			$tangoIcon = $path;
		}
        return $this->createElement('img', false, array(
				'src' => $tangoIcon,
				'alt' 	=> $title,
				'title' => $title,
				'width' => $len,
				'height' => $len
			));
 	}
	
	
	function selectorBox()
	{
		return "\n<span id=\"BCMSNavigator\">";
		
	}
	
	function endSelectorBox()
	{
		
		return "\n</span>";
	}
	function applicationSelector($applications)
	{
		$html = "\n<div id=\"BCMSSwitchOptApp\"><div class=\"side-scroll-body\">";
		$i = 0;
		$ctab = isset($_GET['tab'])
			?(get_magic_quotes_gpc() ? stripslashes($_GET['tab']) : $_GET['tab'])
			: '';
		$tr = Translation::alloc();
		$tr->init();
        foreach($applications as $identifier => $infos)
        {
        	$tabs = '';
            foreach ($infos['tabs'] as $tab => $icon) 
        	{
        		$activeTab = $ctab == $tab && $identifier == BAMBUS_APPLICATION;
        		$tabs .= sprintf(
        			'<a class="%sapplication_tab"  href="%s">%s%s<br class="clear" /></a>'
        			,$activeTab ? 'active ' : ''
        			,$this->createQueryString(array('editor' => $infos['item'], 'tab' => $tab),true)
        			,$this->icon($icon,'',null,$activeTab ? 'small' : 'extra-small', $activeTab)
        			,$tr->treturn($tab)
        		);
        	}
        	$html .= sprintf(
				"\t<div class=\"%sbambus_type_application\">".
//					"%s\n\t\t".
					"<h2 class=\"ApplicationTitle\">%s%s</h2>\n\t\t".
        			"%s".
					"</div>\n"
				,$identifier == BAMBUS_APPLICATION ? 'active ' : ''
				,$this->icon($infos['icon'],$tr->treturn($infos['desc']),'app','small', $identifier == BAMBUS_APPLICATION)
				,$tr->treturn($infos['name'])
				,$tabs
 			);
            $i++;
        }
        $html .= "\n</div>\n</div>";
		return $html;
	}
	
	function tabSelector($tabs)
	{
		return '';
//		$html = "\n<div id=\"BCMSSwitchOptTab\">";
//		$i = 0;
//		$tr = Translation::alloc();
//		$tr->init();
//        foreach($tabs as $identifier => $infos)
//        {
//         	$html .= '';sprintf(
//				"\t<a class=\"%s\" href=\"%s\">%s\n\t\t" .
//					"<span class=\"ApplicationTitle\">%s</span>\n\t\t" .
//					"<span class=\"ApplicationDescription\">%s</span>\n\t" .
//				"</a>\n"
//				,$identifier == BAMBUS_APPLICATION_TAB ? 'active ' : ''
//				,$this->createQueryString(array('tab' => $identifier))
//				,$this->icon($infos[0],'',false,'large')
//				,$tr->treturn($infos[1])
//				,''
// 			);
//            $i++;
//        }       
//		$html .= "\n</div>";
//		return $html;
	}
	
    function iconBar($tabs = array(),$tabname = 'tab')
    {
     
     return '';
//     
//        $get = &$this->get;
//        if(count($tabs) > 0)
//        {
//            $keys = array_keys($tabs);
//            $span = '';
//            $i = 1;
//            foreach($tabs as $identifier => $caption)
//            {
//            	//tab empty or wrong -> set to first item
//                if(empty($get[$tabname]) || !in_array($get[$tabname], $keys))
//                {
//                    $get[$tabname] = $keys[0];
//                }
//                $image = $this->icon($caption[0],$caption[1], false, 'medium');
//                if($get[$tabname] = $keys[$i])
//                {
//                	define('BAMBUS_APPLICATION_TAB_ICON', $this->iconPath($caption[0],$caption[1], false, 'small'));
//                	define('BAMBUS_APPLICATION_TAB_TITLE', $caption[1]);
//                	
//                }
//                $accesskeyinfo = $this->createElement('span','^'.$i, array('class' => "accesskeyinfo"));
//                $link = $this->createElement('a', $accesskeyinfo.$image, array('accesskey' => $i, 'href' => $this->Linker->createQueryString(array($tabname => $identifier))));
//                $span .= $this->createElement('span',$link, array('class' => (($get[$tabname] === $identifier) ? 'Selected' : '')."TabItem"));
//                $i++;
//            }
//            
//        }
//        if(!defined('BAMBUS_APPLICATION_TAB_ICON'))
//        {
//        	define('BAMBUS_APPLICATION_TAB_ICON', '');
//        	define('BAMBUS_APPLICATION_TAB_TITLE', '');
//        }
//        return $this->createElement('div', $span, array('id' => "iconbar", 'class' => 'noaccesskeys'));
    }
    
    function beginMultipartForm($_get_values = array(), $id = '')
    {
        if(!is_array($_get_values))$_get_values = array();
        return sprintf("<form enctype=\"multipart/form-data\" action=\"%s\"%s method=\"post\">\n", $this->Linker->createQueryString($_get_values), (empty($id)) ? '' : sprintf(' id="%s"', $id).(($id == 'documentform') ? ' name="documentform"' : ''));
    }
    
    function endMultipartForm()
    {
        return "</form>\n";
    }
    
    function beginTaskBar()
    {
        return "<div id=\"taskbar\" class=\"nohotkeys\">\n";
    }
    
    function taskSpacer()
    {
		return $this->createElement('div', '', array('class' => "taskspacer"));
    }
    
    function beginTaskObject()
    {
    	return "<div class=\"taskitem\">\n";	
    }
    
    function endTaskObject()
    {
    	return "</div>\n";	
    }
    
    function userRequest($icon, $requestmessage, $system = true)
    {
    	if(substr($icon, -4, 1) == '.') $icon = substr($icon, 0, -4);
    	$image = $this->icon($icon, '', 'mimetype','medium', $system);
		$br = $this->createElement('br', false, array('class' => "clear"));
		$image = $this->createElement('span', $image, array('style' => "float:left;padding:4px;padding-top:0px;"));
    	return $this->createElement('div', $image.$requestmessage.$br, array('class' => "userrequest"));
    }
    var $hotKeys = array();
    function taskButton($Action, $IsJSAction, $icon, $Caption, $hotkey = '')
    {
		$hotkeyinfo = '';
		if (!empty($hotkey))
		{
    		$keyCode = ord($hotkey);
			$this->hotKeys[$hotkey] = array(($IsJSAction) ? $Action : str_replace('&amp;', '&', $Action), true);
			if($keyCode >= 65 && $keyCode<=90) // A-Z
			{
				$hotkey = '^'.$hotkey;
			}
			$hotkeyinfo = $this->createElement('span',$hotkey, array('class' => "hotkeyinfo"));
		}
        $image = $this->icon($icon,$Caption, false, 'small');
        
        $Action = "javascript:".$Action;
    	$a = $this->createElement('a', $hotkeyinfo.$image, array('href' => $Action, 'title' => $Caption.''));
    	$e = $this->createElement('div', $a, array('class' => "taskbutton"));
    	return $e;
    }
    
    function toolButton($Action, $ImgOrTag, $IsTag, $Caption = '')
    {
        $out = '<a title="'.$Caption.'" href="javascript:'.$Action.'">';
        $out .= ($IsTag) ? '<'.$ImgOrTag.'>'.$Caption.'</'.$ImgOrTag.'>' : '<img src="'.parent::pathTo('systemImage').$ImgOrTag.'.png\" alt="'.$Caption.'" />';
        $out .= '</a>';
        return "<div class=\"toolbutton\">\n".$out."</div>";
    }
    
    function endTaskBar()
    {
    	$out = "<br class=\"clear\" /></div>\n";
        if($this->hotKeys != array())
        {
        	$out .= $this->beginScript();
        	foreach($this->hotKeys as $hk => $fx)
        	{
        		$keyCode = ord($hk);
        		if($fx[1])//is js link
					$out .= sprintf("addHotKeyListener(%d, \"%s\");\n", $keyCode, $fx[0]);
        	}
        	$out .= $this->endScript();
        }
        return $out;
        
    }
    
    function beginForm($_get_values = array(), $id = '')
    {
        if($id == 'documentform')
        {
        	$id = 'documentform" name="documentform';
        }
        $id = (empty($id)) ? '' : ' id="'.$id.'"';
        if(!is_array($_get_values))$_get_values = array();
        $action = $this->Linker->createQueryString($_get_values);
        return "<form action=\"".$action."\" method=\"post\"".$id.">\n";
    }
    
    function endForm()
    {
        return "</form>\n";
    }
    
    function hiddenInput($name, $value, $id = '')
    {
        if(!empty($id)){
            $id = 'id="'.$id.'" ';
        }
        return "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\" ".$id."/>\n";
    }
    
    function beginApplication()
    {
        return "<div id=\"BambusContentArea\">\n<div id=\"BambusApplication\">\n";
    }
    
    function endApplication()
    {
        return "</div>\n</div>\n";
    }
    
    function beginScript()
    {
        return "<script type= \"text/javascript\">\n";
    }
    
    function endScript()
    {
        return "</script>\n";
    }
    
    function script($jsCommands)
    {
    	return $this->beginScript().$jsCommands.$this->endScript();
    }
    
    function beginEditor()
    {
        return "<div id=\"editor\">\n";
    }
    
    function endEditor()
    {
        return "</div>\n";
    }
    
    function editorTextarea($content, $spellcheck = false)
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
    
    function beginNavigator()
    {
        return "<div id=\"sidebar\">\n";
    }
    
    function endNavigator()
    {
        return "</div>\n";
    }
    
    function beginCategorySelector()
    {
    	return "<div id=\"categorySelector\">\n";
    }
    
    function endCategorySelector()
    {
    	return "</div>\n";
    }
    
    function beginToolbar()
    {
        return "<div class=\"editortoolbar\">\n";
    }
    
    function endToolbar()
    {
        return "</div>\n";
    }
    
    function beginBox($class = 'tabbox')
    {
        return "<div class=\"".$class."\">\n";
    }
    
    function endBox()
    {
        return "</div>\n";
    }
    
    function verticalSpace()
    {
        return "<br class=\"clear\"/>";
    }
    
    function beginTable($id = '',$class = 'borderedtable full')
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
    
    function beginTableRow($class = '', $tdclass = "")
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
    
    function tableCellSeperator($class = '')
    {
        if(!empty($class))
        {
            $class = ' class="'.$class.'"';
        }
        return "</td>\n<td".$class.">\n";
    }
    
    function endTableRow()
    {
        return "</td>\n</tr>\n";
    }
    
    function endTable()
    {
        return "</table>\n";
    }
    
    function tableHeader($cells = array())
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
    function tableRow($cells = array())
    {
        $header = '';
        if(is_array($cells) && count($cells) > 0)
        {
            $classes = array_keys($cells);
            $header = "<tr>";
            foreach($classes as $cellid)
            {
                $header .= "<td";
                if(!is_numeric($cellid))
                {
                    $header .= " class=\"".trim($cellid)."\"";
                }
                $header .= ">".$cells[$cellid]."</td>";
            }
            $header .= "</tr>\n";
        }
        return $header;
    }
    
    function beginDocumentSelector($title)
    {
         return "<div class=\"documentSelector\">\n<b>".$title."</b>\n";
    }
    
    function documentSelectorItem($item, $selected, $title)
    {
        return sprintf(
        		'<div class="%swebdoc"><a href="%s"><img alt="" src="%s%s.png" />%s</a><br class="clear"/></div>',
        		(($selected)? 'selected_' : ''),
        		$this->Linker->createQueryString(array('edit' => $item)),
        		parent::pathTo('systemMediumMimeImage'),
        		parent::suffix($item),
        		$title
        	);
    }
    
    function endDocumentSelector()
    {
        return "</div>\n";
    }
}
?>