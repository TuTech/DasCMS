<?php
class Search_Rewriter_SetUser
	implements Interface_Search_Rewriter
{
	public function rewriteSearchRequest(Model_Search_Request $request) {
		if(PAuthentication::isAuthenticated()){
			if(!$request->hasSection('User')){
				$request->addSection('User');
			}
			$request->clearSection('User');
			$request->addRequestElement('User', $request->createRequestElement(PAuthentication::getUserID()));
		}
		else{
			if($request->hasSection('User')){
				$request->removeSection('User');
			}
		}
	}
}
?>
