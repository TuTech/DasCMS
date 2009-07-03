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
	private $codeAssist = true;
	
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
        	,$this->encode(RSent::get($sp, CHARSET))
        );
        //textarea
        $out .= sprintf(
            $textarea
            ,$this->wordWrap   ? ' wrap="on"' : ' wrap="off"'
            ,$this->spellCheck ? ' spellcheck="true"' : ' spellcheck="false"'
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
        
        $out .= sprintf(
            $script
            ,'(function(){'.
                'var h = ($(org.bambuscms.app.document.editorElementId).offsetTop) '.
                    '? function(){return ($(org.bambuscms.app.document.editorElementId).offsetTop+5)*-1;} '.
                    ': -190;'.
                'org.bambuscms.display.setAutosize(org.bambuscms.app.document.editorElementId,0,h);'.
            '})();'
            ,$editorJS
		);
		return $out;
	}
	
	private function encode($string)
	{
	    return htmlentities(mb_convert_encoding($string, CHARSET, 'UTF-8,ISO-8859-1'), ENT_QUOTES, CHARSET);
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