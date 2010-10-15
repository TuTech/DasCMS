org.bambuscms.wnotifications = {
	'fetchServerMessages':function()
	{
		if($("View_UIElement_Notifications-area") && $('notifications'))
		{
			$("View_UIElement_Notifications-area").innerHTML = $('notifications').innerHTML;
		}
	},
	'MESSAGE':'message',
	'INFORMATION':'information',
	'WARNING':'warning',
	'ALERT':'alert'
};
org.bambuscms.wnotifications.report = function(type, message)
{
	type = type.toLowerCase();
	switch(type)
	{
		case 'message':
		case 'information':
		case 'warning':
		case 'alert':
			var html = $c('div');
			html.className = type;
			html.appendChild($t(message));
			if($("View_UIElement_Notifications-area"))
			{
				if($("View_UIElement_Notifications-area").firstChild && $("View_UIElement_Notifications-area").firstChild.nodeType == 1 && $("View_UIElement_Notifications-area").firstChild.tagName.toUpperCase() == 'P')
				{
					$("View_UIElement_Notifications-area").innerHTML = '';
				}
				if(!$("View_UIElement_Notifications-area").firstChild)
				{
					$("View_UIElement_Notifications-area").appendChild(html);
				}
				else
				{
					$("View_UIElement_Notifications-area").insertBefore(html, $("View_UIElement_Notifications-area").firstChild);
				}
			}
	}
}
org.bambuscms.autorun.register(org.bambuscms.wnotifications.fetchServerMessages);
