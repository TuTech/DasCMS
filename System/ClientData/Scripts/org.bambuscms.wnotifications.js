org.bambuscms.wnotifications = {
	'fetchServerMessages':function()
	{
		if($("WNotifications-area") && $('notifications'))
		{
			$("WNotifications-area").innerHTML = $('notifications').innerHTML;
		}
	}
};
org.bambuscms.autorun.register(org.bambuscms.wnotifications.fetchServerMessages);
