org.bambuscms.wsidebar = {
	'currentObject':null,
	'show':function(widget, widgetObj)
	{
		var i;
		var selectors = $('WSidebar-select').getElementsByTagName('a');
		for(i = 0; i < selectors.length; i++)
		{
			selectors[i].className = '';
		}
		$('WSidebar-selected').value = widget;
		$('WSidebar-selector-'+widget).className = 'selectedWidget';
		var body = $('WSidebar-body');
		elements = body.getElementsByTagName('div');
		for(i = 0; i < elements.length; i++)
		{
			if(elements[i].className == 'WSidebar-child')
				elements[i].style.display = 'none';
		}
		$('WSidebar-child-'+widget).style.display = 'block';
		if(org.bambuscms.wsidebar.currentObject)
		{
			window.setTimeout(org.bambuscms.wsidebar.currentObject+'.hide()', 1);
		}		
		if(widgetObj)
		{
			org.bambuscms.wsidebar.currentObject = widgetObj;
			window.setTimeout(widgetObj+'.show()', 1);
		}
		else
		{
			org.bambuscms.wsidebar.currentObject = null;
		}
		return false;
	}
}
