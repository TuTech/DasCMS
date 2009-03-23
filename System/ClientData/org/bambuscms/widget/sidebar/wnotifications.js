org.bambuscms.wnotifications = {
	'fetchServerMessages':function()
	{
		if($("WNotifications-area") && $('notifications'))
		{
			$("WNotifications-area").innerHTML = $('notifications').innerHTML;
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
			html.appendChild(document.createTextNode(message));
			if($("WNotifications-area"))
			{
				if($("WNotifications-area").firstChild && $("WNotifications-area").firstChild.nodeType == 1 && $("WNotifications-area").firstChild.tagName.toUpperCase() == 'P')
				{
					$("WNotifications-area").innerHTML = '';
				}
				if(!$("WNotifications-area").firstChild)
				{
					$("WNotifications-area").appendChild(html);
				}
				else
				{
					$("WNotifications-area").insertBefore(html, $("WNotifications-area").firstChild);
				}
			}
	}
}
org.bambuscms.autorun.register(org.bambuscms.wnotifications.fetchServerMessages);
