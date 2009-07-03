org.bambuscms.app.helper = {};

org.bambuscms.app.helper.create = function(forElement, withElement)
{
	if(typeof forElement == 'string')
	{
		forElement = $(forElement);
	}
	var frame = $c('div');
	frame.className = 'org_bambuscms_app_helper_frame';
	frame.appendChild(withElement);
	if(forElement.style.width)
	{
		frame.style.width = forElement.style.width;
	}
	if(forElement.nextSibling)
	{
		forElement.parentNode.insertBefore(frame, forElement.nextSibling);
	}
	else
	{
		forElement.parentNode.appendChild(frame);
	}
	return frame;
}

org.bambuscms.app.helper.remove = function(helper)
{
	helper.parentNode.removeChild(helper);
}

