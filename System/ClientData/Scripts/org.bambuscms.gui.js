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
org.bambuscms.gui.setEventHandler = function(element, event, handler)
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
org.bambuscms.gui.switchButtonCallBacks = {};
org.bambuscms.gui.switchButton = function(name, states)
{
	//name: string
	//states: array of objects
	var cssClass;
	var container = org.bambuscms.gui.element('div', null, {
		'class':'WSwitchButton', 
		'id':'WSB_'+name,
		'style': 'width:'+(states.length*49+9)+'px'
	});
	org.bambuscms.gui.switchButtonCallBacks[name] = [];
	for(var i = 0; i < states.length; i++)
	{
		if(i != 0 && i < states.length - 1)
		{
			cssClass = 'WSB_middle';
		}
		else
		{
			cssClass = (i == 0) ? 'WSB_left WSB_active' : 'WSB_right';
		}
		cssClass += ' WSB_button';
		var button = org.bambuscms.gui.element('a', states[i].title, {
			'class':cssClass,
			'href':'javascript:org.bambuscms.gui.switchButtonClick(\'WSB_'+name+'\', '+i+');'
		});
		org.bambuscms.gui.switchButtonCallBacks[name][i] = states[i].callBack;
		container.appendChild(button);
	}
	return container;
}
org.bambuscms.gui.switchButtonClick = function(id, state)
{
	var sb = $(id);
	var name = id.replace(/^WSB_/, '');
	for(var i = 0; i < sb.childNodes.length; i++)
	{
		sb.childNodes[i].className = sb.childNodes[i].className.replace(/\s+WSB_active/, '');
	}
	sb.childNodes[state].className += ' WSB_active';
	if(org.bambuscms.gui.switchButtonCallBacks[name] && org.bambuscms.gui.switchButtonCallBacks[name][state])
	{
		org.bambuscms.gui.switchButtonCallBacks[name][state]();
	}
}

