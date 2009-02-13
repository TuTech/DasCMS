<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 23.03.2008
 * @license GNU General Public License 3
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
	    $headList = new WList(array(),'li','ul',($title != null ? (SLocalization::get($title)) : ''));
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
	        case 'paragraph_types':
	            return sprintf(
	            	"<a href=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('formatblock','<%s>')\">%s</a>"
	                ,$tag
	                ,$name
	            );
	        case 'char_formats':
	            return sprintf(
	            	"<a href=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('%s')\">%s</a>"
	                ,$name
	                ,$name
	            );
	        case 'insert':
	            return sprintf(
	            	"<a href=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('%s')\">%s</a>"
	                ,$tag
	                ,$name
	            );
            case 'commands':
	            if($tag == 'source')
	            {
	                return sprintf(
    	            	"<a href=\"javascript:alert(org.bambuscms.editor.wysiwyg.editors[0].getText());\">%s</a>"
    	                ,$tag
    	                ,$name
    	            );
	            }
	            else
                {    	            
                    return sprintf(
    	            	"<a href=\"javascript:org.bambuscms.editor.wysiwyg.editors[0].exec('%s')\">%s</a>"
    	                ,$tag
    	                ,$name
    	            );
	            }
            default:
	            return $name;
	    }
	}

	private static function getStruct()
	{
	    return array(
	        'commands' => array('removeformat' => 'remove format','unlink' => 'remove link','source' => 'show source'),
	    	'headings' => array('H1'=>'huge','H2'=>'large','H3'=>'medium large','H4'=>'medium small','H5'=>'small','H6'=>'tiny'),
	        'paragraph_types' => array('p'=>'paragraph','address'=>'address','pre'=>'prefromated text'),
	        //'paragraph_formats' => array('css here'),
	        'char_formats' => array('bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript'),
	        'insert'=> array('createlink' => 'external link'/*, 'link to content', 'embed image'*/)
	    );
	}
	
	
	public function render()
	{
	    echo '<div id="WWYSIWYGPanel">';
	    print('<script type="text/javascript">org.bambuscms.wwysiwygpanel.target = null;org.bambuscms.autorun.register(function(){org.bambuscms.wwysiwygpanel.target = org.bambuscms.editor.wysiwyg.editors[0];});</script>');
	    echo $this->makeList(self::getStruct());
//	    $pTypes = new WList(array(),'li', 'ul',SLocalization::get('paragraph_types'));
//	    $pTypes->add(new WList(array('H1','H2','H3','H4','H5','H6'), 'li', 'ul', 'headings'));
//	    $pTypes->add(new WList(array('paragraph','address','code','quote','without_type'), 'li', 'ul', 'other'));
//	    echo $pTypes;
//	    $pTypes = new WList(array(),'li', 'ul',SLocalization::get('paragraph_formats'));
//	    printf('<h3>%s</h3>', SLocalization::get('paragraph_formats'));
//	    printf('<h3>%s</h3>', SLocalization::get('char_formats'));
//	    printf('<h3>%s</h3>', SLocalization::get('insert'));
	    echo '</div>';
	}
}
?>