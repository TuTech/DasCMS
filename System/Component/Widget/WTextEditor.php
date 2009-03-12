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
class WTextEditor extends BWidget 
{
	const CLASS_NAME = "WTextEditor";
	private $value;
	private $spellCheck = true;
	private $wordWrap = true;
	private $wysiwyg = false;
	
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
        return $this->wordWrap;
    }
    
    public function setWYSIWYG($yn)
    {
        $this->wordWrap = $yn == true;
    }
    
	/**
	 * get render() output as string
	 * @return string
	 */
	public function __toString()
	{
	    $hidinp = '<input type="hidden" name="%s" id="%s" value="%s" />'."\n";
        $textarea = '<textarea name="content" class="WCodeEditor" id="org_bambuscms_app_document_editorElementId"%s%s>%s</textarea>'."\n";
	    $script = '<script type="text/javascript">%s%s</script>'."\n";
        $sp = 'org_bambuscms_wcodeeditor_scrollpos';
        //scrollpos
        $out = sprintf(
        	$hidinp
        	,$sp
        	,$sp
        	,$this->encode(RSent::get($sp, 'utf-8'))
        );
        //textarea
        $out .= sprintf(
            $textarea
            ,$this->wordWrap   ? ' wrap="on"' : ' wrap="off"'
            ,$this->spellCheck ? ' spellcheck="true"' : ' spellcheck="false"'
            ,$this->value
        );
        //javascript resize/wysiwyg
        $out .= sprintf(
            $script
            ,'(function(){'.
                'var h = ($(org.bambuscms.app.document.editorElementId).offsetTop) '.
                    '? function(){return ($(org.bambuscms.app.document.editorElementId).offsetTop+5)*-1;} '.
                    ': -190;'.
                'org.bambuscms.display.setAutosize(org.bambuscms.app.document.editorElementId,0,h);'.
            '})();'
            ,$this->wysiwyg 
                ? 'var editor = org.bambuscms.editor.wysiwyg.create(org.bambuscms.app.document.editorElementId, true);' 
                : 'org.bambuscms.wcodeeditor.run($(org.bambuscms.app.document.editorElementId));'
		);
		return $out;
	}
	
	private function encode($string)
	{
	    return htmlentities(mb_convert_encoding($string, 'UTF-8', 'UTF-8,ISO-8859-1'), ENT_QUOTES, 'UTF-8');
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