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
	'updateInverval':null,
	'update':function(){},
	'updateTime':1000,
	"run":function(textarea)
	{
		if(typeof textarea == 'string')
		{
			textarea = $(textarea);
		} 
		org.bambuscms.wcodeeditor.target = textarea;
		org.bambuscms.gui.setEventHandler(textarea, 'keyup', org.bambuscms.wcodeeditor.keyup);
		org.bambuscms.gui.setEventHandler(textarea, 'keydown', org.bambuscms.wcodeeditor.keydown);
		org.bambuscms.gui.setEventHandler(textarea, 'mouseup', org.bambuscms.wcodeeditor.mouseup);
		org.bambuscms.gui.setEventHandler(textarea, 'mousedown', org.bambuscms.wcodeeditor.mousedown);
		org.bambuscms.wcodeeditor.updateInterval = 
			window.setInterval(org.bambuscms.wcodeeditor.update, org.bambuscms.wcodeeditor.updateTime);
		
		if(org.bambuscms.wcodeeditor.cursor().set)
		{
			var lnr = document.createElement('code');
			lnr.id = 'org_bambuscms_wcodeeditor_infobox_linenr';
			textarea.parentNode.insertBefore(lnr, textarea);
			org.bambuscms.wcodeeditor.refresh[org.bambuscms.wcodeeditor.refresh.length] = function()
			{
				var cursor = org.bambuscms.wcodeeditor.cursor();
				if(cursor.set)
				{
					lnr.innerHTML = cursor.line+':'+cursor.char + (cursor.selected ? ' ['+cursor.selected+']' : '') ;
				}
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
			'char':0,
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
	        result.char = passed.length - (result.line > 1);
	        result.set = true;
	    }
	    return result;
	}
};
