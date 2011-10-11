<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_TextEditor extends _View_UIElement
{
	const CLASS_NAME = "View_UIElement_TextEditor";
	private $value;
	private $spellCheck = true;
	private $wordWrap = true;
	private $wysiwyg = false;
	private $codeAssist = true;
	private $cssClass = '';
	private $attributes = array();

	public function __construct($value = '')
	{
		$this->setContents($value);
	}

    public function disableSpellcheck()
    {
        $this->spellCheck = false;
    }

    public function getContents()
    {
        return $this->value;
    }

    public function setContents($val)
    {
        $this->value = $this->encode($val);
    }

    public function getWordWrap()
    {
        return $this->wordWrap;
    }

    public function setWordWrap($yn)
    {
        $this->wordWrap = $yn == true;
    }

    public function getWYSIWYG()
    {
        return $this->wysiwyg;
    }

    public function setWYSIWYG($yn)
    {
        $this->wysiwyg = $yn == true;
    }

    public function getCodeAssist()
    {
        return $this->codeAssist;
    }

    public function setCodeAssist($yn)
    {
        $this->codeAssist = $yn == true;
    }

	public function addCssClass($class){
		$this->cssClass .= ' '.$class;
	}

	public function addCustomAttribute($name, $value){
		$this->attributes[$name] = $value;
	}

	/**
	 * get render() output as string
	 * @return string
	 */
	public function __toString()
	{
	    $hidinp = '<input type="hidden" name="%s" id="%s" value="%s" />'."\n";
        $textarea = '<textarea name="content" class="WCodeEditor%s" id="org_bambuscms_app_document_editorElementId"%s%s%s>%s</textarea>'."\n";
	    $script = '<script type="text/javascript">%s%s</script>'."\n";
        $sp = 'org_bambuscms_wcodeeditor_scrollpos';
        //scrollpos
        $out = sprintf(
        	$hidinp
        	,$sp
        	,$sp
        	,$this->encode(RSent::get($sp, CHARSET))
        );
		$customAtts = '';
		foreach ($this->attributes as $k => $v){
			$customAtts .= sprintf(' %s="%s"',  String::htmlEncode($k), String::htmlEncode($v));
		}
        //textarea
        $out .= sprintf(
            $textarea
			,$this->cssClass
            ,' wrap="off"'//FIXME safari breaks links with saving generated word wraps //was: $this->wordWrap   ? ' wrap="on"' : ' wrap="off"'
            ,$this->spellCheck ? ' spellcheck="true"' : ' spellcheck="false"'
			,$customAtts
            ,$this->value
        );
        //javascript resize/wysiwyg
        $editorJS = '';
        if($this->wysiwyg)
        {
            //WYSIWYG
            $editorJS = 'var editor = org.bambuscms.editor.wysiwyg.create(org.bambuscms.app.document.editorElementId, true);';
        }
        elseif($this->codeAssist)
        {
            //assisting textarea
            $editorJS = 'org.bambuscms.wcodeeditor.run($(org.bambuscms.app.document.editorElementId));';
		}
		return $out;
	}

	private function encode($string)
	{
	    return String::htmlEncode($string);
	}

	public function render()
	{
	    echo strval($this);
	}

	public function run()
	{
	}
	/**
	 * return ID of primary editable element or null
	 *
	 * @return string
	 */
	public function getPrimaryInputID()
	{
		return "org_bambuscms_app_document_editorElementId";
	}
}
?>