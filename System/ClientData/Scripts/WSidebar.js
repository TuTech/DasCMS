function WSidebarShow(widget)
{
	var body = document.getElementById('WSidebar-body');
	elements = body.getElementsByTagName('div');
	for(var i = 0; i < elements.length; i++)
	{
		if(elements[i].className == 'WSidebar-child')
			elements[i].style.display = 'none';
	}
	document.getElementById('WSidebar-child-'+widget).style.display = 'block';
}