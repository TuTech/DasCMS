<?php
/**
 * Description of RemoveScripts
 *
 * @author lse
 */
class HTMLCleaner_Cleaner_RemoveScripts implements HTMLCleaner_Cleaner {
	public function clean(DOMNode $node){
		if($node->nodeType == XML_ELEMENT_NODE
				&& strtolower($node->localName) == 'script')
		{
			return false;
		}
		
		//remove all on* attributes
		if($node->nodeType == XML_ELEMENT_NODE){
			$atts = $node->attributes;
			if($atts != null){
				foreach($atts as $name => $attNode){
					if(substr(strtolower($attNode->localName ),0,2) == 'on'){
						$node->removeAttribute($name);
					}
				}
			}
		}
		return true;
	}
}
?>