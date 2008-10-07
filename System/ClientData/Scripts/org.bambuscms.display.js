org.bambuscms.display = {
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
			if(!$(element))
			{
				continue;
			}
			var resizer = org.bambuscms.display._objects[element];
			if(resizer.width)
			{
				var width = (typeof resizer.width == 'function') ? resizer.width() : resizer.width;
				$(element).style.width = ((width < 0) ? org.bambuscms.display.getDocumentWidth() + width : width)+'px';
			}
			if(resizer.height)
			{
				var height = (typeof resizer.height == 'function') ? resizer.height() : resizer.height;
				$(element).style.height = ((height < 0) ? org.bambuscms.display.getDocumentHeight() + height : height)+'px';
			}
		}
	},
	'setAutosize':function(elementID, width, height, refresh)
	{
		org.bambuscms.display._objects[elementID] = {};
		org.bambuscms.display._objects[elementID].width = width;
		org.bambuscms.display._objects[elementID].height = height;
		if(refresh)
		{
			org.bambuscms.display._resize();
		}
	},
	'getDocumentWidth':function(){return 0;},
	'getDocumentHeight':function(){return 0;}
};
(function(){
	org.bambuscms.autorun.register(org.bambuscms.display._init);
})();

