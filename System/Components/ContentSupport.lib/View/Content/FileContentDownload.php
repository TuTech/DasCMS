<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_FileContentDownload
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay() && $this->content instanceof IFileContent){
			$val = $this->wrapXHTML(
				'FileContentDownload',
				sprintf(
					"<a href=\"file.php/%s/%s\"%s>%s</a>\n"
					,$this->content->getAlias()
					,urlencode($this->content->getFileName())
					,($this->getLinkTargetFrame() != null) 
						? sprintf(' target="%s"', htmlentities($this->getLinkTargetFrame(), ENT_QUOTES, CHARSET))
						: ''
					,htmlentities((($this->getLinkCaption() != null)
						? $this->getLinkCaption()
						: $this->content->getFileName()), ENT_QUOTES, CHARSET)
				),
				false
			);
		}
		return $val;
	}

	public function getLinkCaption() {
		return parent::getLinkCaption();
	}

	public function setLinkCaption($value) {
		parent::setLinkCaption($value);
	}

	public function getLinkTargetFrame() {
		return parent::getLinkTargetFrame();
	}

	public function setLinkTargetFrame($value) {
		parent::setLinkTargetFrame($value);
	}
}
?>