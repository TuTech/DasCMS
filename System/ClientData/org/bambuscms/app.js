org.bambuscms.app = {};
org.bambuscms.app.primarySelectedObjectColor = '#aee27d';
org.bambuscms.app.controller = '';
org.bambuscms.app.search = function(query){
	if(org.bambuscms.app.searchCallBack && typeof org.bambuscms.app.searchCallBack == 'function')
	{
		org.bambuscms.app.searchCallBack(query);
	}
};