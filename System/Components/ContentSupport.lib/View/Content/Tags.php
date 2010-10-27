<?php
/**
 * Description of Tags
 *
 * @author lse
 */
class View_Content_Tags
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			$tags = $this->content->getTags();
			$list = '';
			if(count($tags)){
				$list = '<ul>';
				foreach ($tags as $tag){
					if(substr($tag,0,1) != '@' /* control tags */){
						$list .= sprintf("<li>%s</li>\n", String::htmlEncode($tag));
					}
				}
				$list .= '</ul>';
			}
			$val = $this->wrapXHTML('Tags', $list);
		}
		return $val;
	}
}
?>