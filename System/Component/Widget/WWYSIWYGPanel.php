<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-03-23
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class WWYSIWYGPanel extends BWidget implements ISidebarWidget 
{
	private $targetObject = null;
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public static function isSupported(WSidePanel $sidepanel)
	{
	    return (
	        $sidepanel->hasTarget()
	        && $sidepanel->isTargetObject()
	        && $sidepanel->isMode(WSidePanel::WYSIWYG)
	        && LConfiguration::get('use_wysiwyg') == '1'
	    );
	}
	
	public function getName()
	{
	    return 'wysiwyg_panel';
	}
	
	public function getIcon()
	{
	    return new WIcon('format','',WIcon::SMALL,'action');
	}
	
	public function processInputs()
	{
	}
	
	public function __construct(WSidePanel $sidepanel)
	{
		$this->targetObject = $sidepanel->getTarget();
	}
	
	public function __toString()
	{
	    ob_start();
	    $this->render();
	    $html = strval(ob_get_clean());
		return $html;
	}
	
	private function makeList(array $ofItems, $title = null)
	{
	    $headList = (in_array($title, array('char_formats', 'headings', 'paragraph_types', 'layout')))
	        ? (new WList(array(),null,'div',($title != null ? (SLocalization::get($title)) : '')))
	        : (new WList(array(),'li','ul',($title != null ? (SLocalization::get($title)) : '')));
	    foreach($ofItems as $head => $item)
	    {
	        if(is_array($item))
	        {
	            $data = $this->makeList($item,$head);
	        }
	        else
	        {
	            $data = $this->makeLink($title, $head, $item);
	        }
	        $headList->add($data);
	    }
	    return $headList;
	}
	
	private function makeLink($section, $tag, $name)
	{
	    switch($section)
	    {
	        case 'headings':
	            return sprintf(
	            	"<img src=\"%s\" alt=\"%s\" title=\"%s\" onclick=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('formatblock','<%s>')\">"
	                ,WIcon::pathFor('format-'.$tag, 'action',WIcon::SMALL)
	                ,$name
	                ,htmlentities(SLocalization::get($name), ENT_QUOTES, 'UTF-8')
	                ,$tag
                );
	        case 'paragraph_types':
	            return sprintf(
	            	"<img src=\"%s\" alt=\"%s\" title=\"%s\" onclick=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('formatblock','<%s>')\">"
	                ,WIcon::pathFor('format-'.$name, 'action',WIcon::SMALL)
	                ,$name
	                ,htmlentities(SLocalization::get($name), ENT_QUOTES, 'UTF-8')
	                ,$tag
                );
	        //case 'paragraph_types':
	            return sprintf(
	            	"<a href=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('formatblock','<%s>')\">%s</a>"
	                ,$tag
	                ,htmlentities(SLocalization::get($name), ENT_QUOTES, 'UTF-8')
	            );
	        case 'layout':
                $icon = str_replace('justify', 'align-', $tag);
	            return sprintf(
	            	"<img src=\"%s\" alt=\"%s\" title=\"%s\" onclick=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('%s')\">"
	                ,WIcon::pathFor('format-'.$icon, 'action',WIcon::SMALL)
	                ,$name
	                ,htmlentities(SLocalization::get($name), ENT_QUOTES, 'UTF-8')
	                ,$tag
                );	            
            case 'char_formats':
	            return sprintf(
	            	"<img src=\"%s\" alt=\"%s\" title=\"%s\" onclick=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('%s')\">"
	                ,WIcon::pathFor('format-'.$tag, 'action',WIcon::SMALL)
	                ,$name
	                ,htmlentities(SLocalization::get($name), ENT_QUOTES, 'UTF-8')
	                ,$name
                );	            
	        case 'insert':
	            return sprintf(
	            	"<a href=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('%s')\">%s</a>"
	                ,$tag
	                ,htmlentities(SLocalization::get($name), ENT_QUOTES, 'UTF-8')
	            );
            case 'commands':
	            if($tag == 'switchWYSIWYG')
	            {
	                return sprintf(
    	            	"<a href=\"javascript:void(org.bambuscms.editor.wysiwyg.editors[0].switchWYSIWYG());\">%s</a>"
    	                ,htmlentities(SLocalization::get($name), ENT_QUOTES, 'UTF-8')
    	            );
	            }
	            else
                {    	            
                    return sprintf(
    	            	"<a href=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('%s')\">%s</a>"
    	                ,$tag
    	                ,htmlentities(SLocalization::get($name), ENT_QUOTES, 'UTF-8')
    	            );
	            }
            default:
	            return htmlentities(SLocalization::get($name), ENT_QUOTES, 'UTF-8');
	    }
	}

	private static function getStruct()
	{
	    return array(
	    	'headings' => array('h1'=>'huge','h2'=>'large','h3'=>'medium_large','h4'=>'medium_small','h5'=>'small','h6'=>'tiny'),
	        'paragraph_types' => array('p'=>'paragraph','address'=>'address'),
	        //'paragraph_formats' => array('css here'),
	        'char_formats' => array('bold' => 'bold', 'italic' => 'italic', 'underline' => 'underline', 'strike' => 'strikethrough', 'sup' => 'superscript', 'sub' => 'subscript'),
	        'layout'=> array('justifyleft' => 'align_left','justifycenter' => 'align_center','justifyright' => 'align_right', 'indent' => 'indent', 'outdent' => 'outdent'),
	        'insert'=> array('createlink' => 'external_link'/*, 'link to content', 'embed image'*/),
	    	'commands' => array('removeformat' => 'remove_format','unlink' => 'remove_link','switchWYSIWYG' => 'show_code_view'),
	    );
	}
	
	
	public function render()
	{
	    echo '<div id="WWYSIWYGPanel">';
	    print('<script type="text/javascript">org.bambuscms.wwysiwygpanel.target = null;org.bambuscms.autorun.register(function(){org.bambuscms.wwysiwygpanel.target = org.bambuscms.editor.wysiwyg.editors[0];});</script>');
	    echo '<div id="WWYSIWYGPanel-Design">';
	    echo $this->makeList(self::getStruct());
	    echo '</div><div id="WWYSIWYGPanel-Code" style="display:none">';
	    echo $this->makeList(array('commands' => array('switchWYSIWYG' => 'show design view')));
	    echo '</div></div>';
	}
	
	public function associatedJSObject()
	{
	    return null;
	}
}
?>