<?php
class Search_Rewriter_SetUser
	implements Search_Rewriter
{
	public function rewriteSearchRequest(Search_Request $request) {
		if(PAuthentication::isAuthenticated()){
			if(!$request->hasSection('User')){
				$request->addSection('User');
			}
			$request->clearSection('User');
			$request->addRequestElement('User', $request->createRequestElement(PAuthentication::getUserID()));
		}
	}
}
?>
