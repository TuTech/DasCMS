org.bambuscms.app = {};
org.bambuscms.app.initialize = function()
{
	if($(org.bambuscms.app.notifier.elementId))
	{
		org.bambuscms.app.notifier.hide(2000, 0, 1800, 50);
	}
	org.bambuscms.app.initialize = function(){};
};
org.bambuscms.app.primarySelectedObjectColor = '#aee27d';
org.bambuscms.autorun.register(org.bambuscms.app.initialize);