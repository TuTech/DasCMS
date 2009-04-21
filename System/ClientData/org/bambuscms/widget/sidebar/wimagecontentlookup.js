org.bambuscms.wimagecontentlookup = {};
org.bambuscms.wimagecontentlookup.filter = function()
{
	org.bambuscms.wimagecontentlookup.fetch();
}
org.bambuscms.wimagecontentlookup.generateList = function(respObject)
{
	if(respObject.items)
	{
		var html = $c('ul');
		var items = respObject.items;
		for(var i = 0; i < items.length; i++)
		{
			var item = $c('li');
			item.setAttribute('onclick', "org.bambuscms.app.document.insertMedia('image','image.php/"+items[i][0]+"', '"+items[i][1].replace(/'/,"\\'")+"');");
			item.setAttribute('title', respObject.type[items[i][3]]+': '+items[i][0]);
			item.style.backgroundImage = 'url(image.php/'+items[i][0]+'/48-32-0-f-ff-ff-ff)';//72x50
			
			if(items[i][2] <= 0)item.className = 'unpublished';
			else if(items[i][2] <= respObject.now)item.className = 'published';
			else item.className = 'publicationScheduled';
			if(i%2)item.className += ' alt';
			item.appendChild(document.createTextNode(items[i][1]));
			
			var pd = $c('span');
			var d = new Date(items[i][2]*1000);
			var text = (items[i][2] <= 0) ? _('not_public') : d.toUTCString();

			pd.appendChild(document.createTextNode(text));
			item.appendChild(pd);
			html.appendChild(item);
		}
		$('WImageContentLookup').innerHTML = '';
		$('WImageContentLookup').appendChild(html);
	}
}
org.bambuscms.wimagecontentlookup.fetch = function()
{
	var data = {
		'controller':org.bambuscms.app.controller,
		'call':'provideImageContentLookup'
	};
	var send = {
		'filter':'',
		'mode':'all'
	};
	if($('WImageContentLookupFilter'))
	{
		send.filter = $('WImageContentLookupFilter').value;
	}
	if($('WImageContentLookupMode'))
	{
		send.mode = $('WImageContentLookupMode').options[$('WImageContentLookupMode').selectedIndex].value;
	}
	send = org.json.stringify(send);
	var qobj = org.bambuscms.http.managementRequestURL(data);
	org.bambuscms.http.fetchJSONObject(
		qobj,
		org.bambuscms.wimagecontentlookup.generateList,
		send
	);
}
org.bambuscms.autorun.register(function(){
	if($('WImageContentLookup'))
	{
		org.bambuscms.wimagecontentlookup.fetch();
		org.bambuscms.gui.setEventHandler($('WImageContentLookupFilter'), 'keyup',   org.bambuscms.wimagecontentlookup.fetch);
		org.bambuscms.gui.setEventHandler($('WImageContentLookupFilter'), 'mouseup', org.bambuscms.wimagecontentlookup.fetch);
	}
	if($('WImageContentLookupMode'))
	{
		org.bambuscms.gui.setEventHandler($('WImageContentLookupMode'), 'change', org.bambuscms.wimagecontentlookup.fetch);
	}
});