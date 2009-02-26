org.bambuscms.autorun = {
	"register":function(func)
	{
		if(typeof func == 'function')
		{
			if(org.bambuscms.autorun.done)
			{
				func();
			}
			else
			{
				org.bambuscms.autorun.executionStack.push(func);
			}
		}
	},
	'_init':function()
	{
		if(window.addEventListener)
			window.addEventListener('load', org.bambuscms.autorun.run, false);
		else if(window.attachEvent)
			window.attachEvent('onload', org.bambuscms.autorun.run);
		else
			window.onload = org.bambuscms.autorun.run;
	},
	"run":function()
	{
		for(var i = 0; i < org.bambuscms.autorun.executionStack.length; i++)
		{
			try{
				org.bambuscms.autorun.executionStack[i]();
			}catch(e){/* continue with next in stack */}
		}
		org.bambuscms.autorun.done = true;
	},
	'done':false,
	'executionStack':[]
};
(function(){org.bambuscms.autorun._init();})();