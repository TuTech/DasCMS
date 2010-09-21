<?php
class Search_Rewriter_SetUser
	implements Search_Rewriter
{
	public function rewriteSearchRequest(Search_Request $request) {
		if(PAuthentication::isAuthenticated()){
			if(!$request->hasSection('user')){
				$request->addSection('user');
			}
			$request->clearSection('user');
			$request->addRequestElement('user', $request->createRequestElement(PAuthentication::getUserID()));
		}
	}
}
?>
