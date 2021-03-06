org.bambuscms.display = {
	'callbacks':[],
	'addCallback':function(func){
		if(typeof func == 'function')
		{
			org.bambuscms.display.callbacks[org.bambuscms.display.callbacks.length] = func;
		}
	},
	'_objects':{},
	'_init':function()
	{
		if(window.addEventListener)
			window.addEventListener('resize', org.bambuscms.display._resize, false);
		else if(window.attachEvent)
			window.attachEvent('onresize', org.bambuscms.display._resize);
		else
			window.onresize = org.bambuscms.display._resize;
		/*if(document.documentElement && document.documentElement.scrollWidth)
		{
			org.bambuscms.display.getDocumentWidth = function(){return document.documentElement.scrollWidth;};
			org.bambuscms.display.getDocumentHeight = function(){return document.documentElement.scrollHeight;};
		}
		else if(document.body.scrollWidth)
		{
			org.bambuscms.display.getDocumentWidth = function(){return document.body.scrollWidth;};
			org.bambuscms.display.getDocumentHeight = function(){return document.body.scrollHeight;};
		}
		else*/
		{
			org.bambuscms.display.getDocumentWidth = function(){return window.innerWidth;};
			org.bambuscms.display.getDocumentHeight = function(){return window.innerHeight;};
		}
		org.bambuscms.autorun.register(org.bambuscms.display._resize);
	},
	'_resize':function()
	{
		for(element in org.bambuscms.display._objects)
		{
			if($(element))
			{
				var resizer = org.bambuscms.display._objects[element];
				if(resizer.width)
				{
					var width = (typeof resizer.width == 'function') ? resizer.width() : resizer.width;
					$(element).style.width = ((width < 0) ? org.bambuscms.display.getDocumentWidth() + width : width)+'px';
				}
				if(resizer.height)
				{
					var height = (typeof resizer.height == 'function') ? resizer.height() : resizer.height;
					height = ((height < 0) ? org.bambuscms.display.getDocumentHeight() + height : height);
					if(height > 0)
					{
						$(element).style.height = height+'px';
					}
				}
			}
		}
		for(var i = 0; i < org.bambuscms.display.callbacks.length; i++){
			org.bambuscms.display.callbacks[i]();
		}
	},
	'setAutosize':function(elementID, width, height, refreshNow)
	{
		org.bambuscms.display._objects[elementID] = {};
		org.bambuscms.display._objects[elementID].width = width;
		org.bambuscms.display._objects[elementID].height = height;
		if(refreshNow)
		{
			org.bambuscms.display._resize();
		}
	},
	'getAutosize':function(elementID)
	{
		if(org.bambuscms.display._objects[elementID])
		{
			return {
					'width':org.bambuscms.display._objects[elementID].width,
					'height':org.bambuscms.display._objects[elementID].height
				};
		}
		return {'width':0,'height':0};
	},
	'getDocumentWidth':function(){return 0;},
	'getDocumentHeight':function(){return 0;}
};
(function(){
	org.bambuscms.autorun.register(org.bambuscms.display._init);
})();

