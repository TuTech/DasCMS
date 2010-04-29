<?php
class Formatter_Attribute_View_FileContentDownload
extends Formatter_Attribute_Link
{
	public function getLinkAlias()
	{
		return $this->getContent()->getAlias();
	}

	protected function getFormatterClass()
	{
		return 'FileContentDownload';
	}

	public function toXHTML($insertString = null)
	{
		if(!$this->getContent() instanceof IFileContent)
		{
			//only active for contents with scope
			return '';
		}
		$str = $this->createDonwnloadLink($this->getContent());
		return _Formatter_Attribute::toXHTML($str);
	}

	protected function createDonwnloadLink(IFileContent $content)
	{
		try
		{
			$insertString = htmlentities($this->getText(), ENT_QUOTES, CHARSET);
			if(empty($insertString)){
				$insertString = $content->getFileName();
			}
			$str = '';
			$targetFrame = $this->getTargetFrame();
			$str = sprintf(
                "<a href=\"file.php/%s/%s\"%s>%s</a>\n"
                ,$this->getLinkAlias()
                ,urlencode($content->getFileName())
                ,empty($targetFrame) ? '' : ' target="'.htmlentities($targetFrame,ENT_QUOTES,CHARSET).'"'
                ,$insertString
			);
		}
		catch (Exception $e)
		{
			$str =  strval($e);
		}
		return $str;
	}

	public function setTargetFrame($frame){}//FIXME
	public function setTargetView($viewName){}//FIXME
}
?>