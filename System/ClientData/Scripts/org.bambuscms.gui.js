org.bambuscms.gui = {};

org.bambuscms.gui.element = function(type, value, attributes)
{
	var elm = document.createElement(type);
	if(value)
	{
		if(typeof value == 'string')
		{
			elm.innerHTML = value;
		}
		else 
		{
			elm.appendChild(value);
		}
	}
	if(attributes && typeof attributes == 'object')
	{
		for(key in attributes)
		{
			elm.setAttribute(key, attributes[key]);
		}
	}
	return elm;
}
org.bambuscms.gui.setEventHandler(element, event, handler)
{
	if(event.substr(0,2) == 'on')
	{
		event = event.substr(2, event.length-2);
	}
	if(element.addEventListener)
	{
		element.addEventListener(event, handler, false);
	}
	else if(element.attachEvent)
	{
		element.attachEvent('on'+event, handler);
	}
	else
	{
		element['on'+event] =  handler;		
	}
}
org.bambuscms.gui.switchButton = function(name, states)
{
	
}