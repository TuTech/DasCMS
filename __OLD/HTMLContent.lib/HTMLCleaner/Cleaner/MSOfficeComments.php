<?php
/**
 * Description of MSOfficeComments
 *
 * @author lse
 */
class HTMLCleaner_Cleaner_MSOfficeComments implements HTMLCleaner_Cleaner {
	public function clean(DOMNode $node){
		return !($node->nodeType == XML_COMMENT_NODE
			&& preg_match('/^\[(if [a-z]+ mso)/mui', $node->nodeValue));
	}
}