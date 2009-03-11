org.bambuscms.wcontentlookup = {};
org.bambuscms.wcontentlookup.filter = function()
{
//	var filterStr
//	if($('WContentLookupFilter') && $('WContentLookup'))
//	{
//		filterStr = $('WContentLookupFilter').value.toLowerCase();
//		var elements = $('WContentLookup').getElementsByTagName('option');
//		var str;
//		for(var i = 0; i < elements.length; i++)
//		{
//			str = elements[i].text.toLowerCase();
//			elements[i].style.display = (str.indexOf(filterStr) > -1) ? 'block' : 'none';
//		}
//	}
	org.bambuscms.wcontentlookup.fetch();
}
org.bambuscms.wcontentlookup.generateList = function(respObject)
{
	if(respObject.items)
	{
		var html = document.createElement('ul');
		var items = respObject.items;
		for(var i = 0; i < items.length; i++)
		{
			var item = document.createElement('li');
			item.setAttribute('onclick', "org.bambuscms.app.document.insertMedia('content','"+items[i][0]+"', '"+items[i][1].replace(/'/,"\\'")+"');");
			item.setAttribute('title', items[i][0]);
			
			if(items[i][2] == 0)item.className = 'unpublished';
			else if(items[i][2] <= respObject.now)item.className = 'published';
			else item.className = 'publicationScheduled';
			if(i%2)item.className += ' alt';
			item.appendChild(document.createTextNode(items[i][1]));
			
			var pd = document.createElement('span');
			var d = new Date(items[i][2]*1000);
			var text = (items[i][2] == 0) ? _('not_public') : d.toLocaleString();

			pd.appendChild(document.createTextNode(text));
			item.appendChild(pd);
			html.appendChild(item);
		}
		$('WContentLookup').innerHTML = '';
		$('WContentLookup').appendChild(html);
	}
}
org.bambuscms.wcontentlookup.fetch = function()
{
	var data = {//FIXME change data source 
		'controller':'org.bambuscms.applications.files',
		'call':'provideContentLookup'
	};
	var send = {
		'filter':'',
		'mode':'all'
	};
	if($('WContentLookupFilter'))
	{
		send.filter = $('WContentLookupFilter').value;
	}
	if($('WContentLookupMode'))
	{
		send.mode = $('WContentLookupMode').options[$('WContentLookupMode').selectedIndex].value;
	}
	send = '{"filter":"'+send.filter.replace(/"/g,'\\"')+'","mode":"'+send.mode+'"}';
	var qobj = org.bambuscms.http.managementRequestURL(data);
	org.bambuscms.http.fetchJSONObject(
		qobj,
		org.bambuscms.wcontentlookup.generateList,
		send
	);
}
org.bambuscms.autorun.register(function(){
	if($('WContentLookup'))
	{
		org.bambuscms.wcontentlookup.fetch();
		org.bambuscms.gui.setEventHandler($('WContentLookupFilter'), 'keyup',   org.bambuscms.wcontentlookup.fetch);
		org.bambuscms.gui.setEventHandler($('WContentLookupFilter'), 'mouseup', org.bambuscms.wcontentlookup.fetch);
	}
	if($('WContentLookupMode'))
	{
		org.bambuscms.gui.setEventHandler($('WContentLookupMode'), 'change', org.bambuscms.wcontentlookup.fetch);
	}
});