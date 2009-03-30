org.bambuscms.wsidebar = {
	'show':function(widget)
	{
		var selectors = $('WSidebar-select').getElementsByTagName('a');
		for(var i = 0; i < selectors.length; i++)
		{
			selectors[i].className = '';
		}
		$('WSidebar-selected').value = widget;
		$('WSidebar-selector-'+widget).className = 'selectedWidget';
		var body = $('WSidebar-body');
		elements = body.getElementsByTagName('div');
		for(var i = 0; i < elements.length; i++)
		{
			if(elements[i].className == 'WSidebar-child')
				elements[i].style.display = 'none';
		}
		$('WSidebar-child-'+widget).style.display = 'block';
		return false;
	}
}
