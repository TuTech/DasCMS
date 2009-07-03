org.bambuscms.app.hotkeys = {};

//onkeypress
//onkeydown
//onkeyup
org.bambuscms.app.hotkeys.triggers = {};

org.bambuscms.app.hotkeys.register = function(hotkey, func)
{
	org.bambuscms.app.hotkeys.triggers[hotkey] = func;
}

org.bambuscms.app.hotkeys.unregister = function(hotkey)
{
	org.bambuscms.app.hotkeys.triggers[hotkey] = null;
}

//'CTRL-o', org.bambuscms.app.document.open
//'CTRL-s', org.bambuscms.app.document.save
//'CTRL-N', org.bambuscms.app.document.createNew
//'CTRL-S', org.bambuscms.app.document.saveAs
//'CTRL-R', org.bambuscms.app.document.searchAndReplace
//'CTRL-X', org.bambuscms.app.document.deleteCurrent
//'CTRL-O', org.bambuscms.app.document.orderContent
//'CTRL-C', org.bambuscms.app.document.cleanContent

org.bambuscms.app.hotkeys.listener = function(e)
{
	var event = e || window.event;
	var code = e.charCode || e.keyCode;
	//if(e.charCode == 0)
	var str = String.fromCharCode(code);
	if(
		((e.ctrlKey || e.metaKey) && code >= 32 && str.match(/^[abd-uwzA-Z0-9]$/))// ctrl/command... hotkeys
		|| (!e.altKey && !e.ctrlKey && !e.shiftKey && !e.metaKey && code == 9)//tab
	)
	{
		var data = 
			'string: '+String.fromCharCode(e.keyCode)+'\n'+
			'keyCode: '+e.keyCode+'\n'+
			'charCode: '+e.charCode+'\n'+
			'altKey: '+e.altKey+'\n'+
			'ctrlKey: '+e.ctrlKey+'\n'+
			'shiftKey: '+e.shiftKey+'\n'+
			'metaKey: '+e.metaKey+'\n';
		
		var hotkey = 
			((e.ctrlKey || e.metaKey) ? 'CTRL-' : '')+
			(e.altKey ? 'ALT-' : '')+
			(code == 9  ? 
				'TAB' : 
				(e.shiftKey ? str.toUpperCase() : str.toLowerCase())
			);
		if(org.bambuscms.app.hotkeys.triggers[hotkey] && 
			typeof org.bambuscms.app.hotkeys.triggers[hotkey] == 'function') 
		{
			var res = org.bambuscms.app.hotkeys.triggers[hotkey]();
			if(!res)
			{
				if(e.preventDefault)e.preventDefault();
				if(e.returnValue)e.returnValue = false;
			}
			return res;
		}
	}
}


org.bambuscms.autorun.register(function(){
	org.bambuscms.gui.setEventHandler(window, 'keydown', org.bambuscms.app.hotkeys.listener);
});