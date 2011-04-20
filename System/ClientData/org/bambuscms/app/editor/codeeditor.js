org.bambuscms.wcodeeditor = {
	"target":null,
	'keyup':function(e){
		org.bambuscms.wcodeeditor.doRefresh();
	},
	'keydown':function(e){
		org.bambuscms.wcodeeditor.doRefresh();
	},
	'mouseup':function(e){
		org.bambuscms.wcodeeditor.doRefresh();
	},
	'mousedown':function(e){
		org.bambuscms.wcodeeditor.doRefresh();
	},
	'doRefresh':function(){
		for(var i = 0; i < org.bambuscms.wcodeeditor.refresh.length; i++)
		{
			org.bambuscms.wcodeeditor.refresh[i]();
		}
	},
	'refresh':[],
	'enableTabToSpace':function(){
		org.bambuscms.app.hotkeys.register('TAB', function(){
			org.bambuscms.app.document.insertText('    ');
			return false;
		});
	},
	'disableTabToSpace':function(){
		org.bambuscms.app.hotkeys.unregister('TAB');
	},
	'updateInverval':null,
	'update':function(){org.bambuscms.wcodeeditor.doRefresh();},
	'updateTime':1000,
	"run":function(textarea)
	{
		if(typeof textarea == 'string')
		{
			textarea = $(textarea);
		} 
		org.bambuscms.wcodeeditor.target = textarea;
		org.bambuscms.gui.setEventHandler(textarea, 'focus', org.bambuscms.wcodeeditor.enableTabToSpace);
		org.bambuscms.gui.setEventHandler(textarea, 'blur', org.bambuscms.wcodeeditor.disableTabToSpace);
		org.bambuscms.gui.setEventHandler(textarea, 'keyup', org.bambuscms.wcodeeditor.keyup);
		org.bambuscms.gui.setEventHandler(textarea, 'keydown', org.bambuscms.wcodeeditor.keydown);
		org.bambuscms.gui.setEventHandler(textarea, 'mouseup', org.bambuscms.wcodeeditor.mouseup);
		org.bambuscms.gui.setEventHandler(textarea, 'mousedown', org.bambuscms.wcodeeditor.mousedown);
		org.bambuscms.wcodeeditor.updateInterval = 
			window.setInterval(org.bambuscms.wcodeeditor.update, org.bambuscms.wcodeeditor.updateTime);
		
		if(org.bambuscms.wcodeeditor.cursor().set)
		{
			var lnr = $c('code');
			lnr.id = 'org_bambuscms_wcodeeditor_infobox_linenr';
			textarea.parentNode.insertBefore(lnr, textarea);
			org.bambuscms.wcodeeditor.refresh[org.bambuscms.wcodeeditor.refresh.length] = function()
			{
				var cursor = org.bambuscms.wcodeeditor.cursor();
				if(cursor.set)
				{
					lnr.innerHTML = cursor.line+':'+cursor.charPos + (cursor.selected ? ' ['+cursor.selected+']' : '') ;
				}
			}
		}
		if($('org_bambuscms_wcodeeditor_scrollpos'))
		{
			var scrollTo = $('org_bambuscms_wcodeeditor_scrollpos').value;
			var parts = scrollTo.split(':');
			if(parts.length == 2)
			{
				org.bambuscms.autorun.register(function(){
					//this function registers another function to be called after everything else in autoload is done 
					org.bambuscms.autorun.register(function(){
						org.bambuscms.wcodeeditor.scrollTo(parts[1], parts[0]);
					});
				});
			}
			org.bambuscms.wcodeeditor.refresh[org.bambuscms.wcodeeditor.refresh.length] = function()
			{
				$('org_bambuscms_wcodeeditor_scrollpos').value = 
					$(org.bambuscms.app.document.editorElementId).scrollTop+':'+$(org.bambuscms.app.document.editorElementId).scrollLeft;
			}
		}
		org.bambuscms.wcodeeditor.doRefresh();
	},
	'cursor':function()
	{
		var result = {
			'start':0,
			'end':0,
			'selected':0,
			'line':0,
			'charPos':0,
			'set':false
		};
		textarea = org.bambuscms.wcodeeditor.target;
	    if(typeof textarea.selectionStart != 'undefined'){
	    	result.start = textarea.selectionStart;           //Cursoranfangsposition
	        result.end = textarea.selectionEnd;               //Cursorendposition
	        result.selected = result.end - result.start;
	        var passed = textarea.value.substring(0,result.start);//text vom Anfang des Textfeldes bis zur Cursorposition
	        var open_tags = passed.match(/\n/g);       
	        result.line = (open_tags) ? open_tags.length + 1 : 1;  
	        passed = (result.line == 1) ? passed : passed.substring(passed.lastIndexOf('\n'));
	        result.charPos = passed.length - (result.line > 1);
	        result.set = true;
	    }
	    return result;
	},
	'scrollTo':function(x,y)
	{
		$(org.bambuscms.app.document.editorElementId).scrollTop = parseInt(y);
		$(org.bambuscms.app.document.editorElementId).scrollLeft = parseInt(x);
	}
};
