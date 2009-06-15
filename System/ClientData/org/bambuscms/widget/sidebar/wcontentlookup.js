org.bambuscms.wcontentlookup = {};
org.bambuscms.wcontentlookup.filter = function()
{
	org.bambuscms.wcontentlookup.fetch();
};
org.bambuscms.wcontentlookup.show = function()
{
	org.bambuscms.wcontentlookup.fetch();
};
org.bambuscms.wcontentlookup.hide = function(){};
org.bambuscms.wcontentlookup.generateList = function(respObject)
{
	if(respObject.items)
	{
		var html;
		if(!respObject.continueList)
		{
			html = $c('ul');
			html.id = 'wcontentlookup-list';
			$('WContentLookup').innerHTML = '';
			$('WContentLookup').appendChild(html);
			var inp = $c('input');
			inp.type = 'hidden';
			inp.id = "wcontentlookup-list-count";
			inp.value = 1;
			$('WContentLookup').appendChild(inp);
			var lnk = $c('a');
			lnk.id = "wcontentlookup-list-more";
			lnk.href =  'javascript:org.bambuscms.wcontentlookup.fetch($(\'wcontentlookup-list-count\').value)';
			lnk.appendChild($t(_('more')));
			$('WContentLookup').appendChild(lnk);
		}
		else
		{
			html = $('wcontentlookup-list');
			inp = $("wcontentlookup-list-count");
		}
		$("wcontentlookup-list-more").style.display = (respObject.hasMore) ? 'block' : 'none';
		var items = respObject.items;
		inp.value = parseInt(inp.value) + 1;
		for(var i = 0; i < items.length; i++)
		{
			var item = $c('li');
			item.setAttribute('onclick', "org.bambuscms.app.document.insertMedia('content','"+items[i][0]+"', '"+items[i][1].replace(/'/,"\\'")+"');");
			item.setAttribute('title', respObject.type[items[i][3]]+': '+items[i][0]);
			
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
	}
};
org.bambuscms.wcontentlookup.fetch = function(more)
{
	var data = {
		'controller':'org.bambuscms.plugin.contentlookup',
		'call':'provideContentLookup'
	};
	var send = {
		'filter':'',
		'mode':'all'
	};
	if(more)
	{
		send.page = more;
	}
	if($('WContentLookupFilter'))
	{
		send.filter = $('WContentLookupFilter').value;
	}
	if($('WContentLookupMode'))
	{
		send.mode = $('WContentLookupMode').options[$('WContentLookupMode').selectedIndex].value;
	}
	send = org.json.stringify(send);
	var qobj = org.bambuscms.http.managementRequestURL(data);
	org.bambuscms.http.fetchJSONObject(
		qobj,
		org.bambuscms.wcontentlookup.generateList,
		send
	);
};
org.bambuscms.autorun.register(function(){
	if($('WContentLookup'))
	{
		org.bambuscms.gui.setEventHandler($('WContentLookupFilter'), 'keyup',   org.bambuscms.wcontentlookup.fetch);
		org.bambuscms.gui.setEventHandler($('WContentLookupFilter'), 'mouseup', org.bambuscms.wcontentlookup.fetch);
	}
	if($('WContentLookupMode'))
	{
		org.bambuscms.gui.setEventHandler($('WContentLookupMode'), 'change', org.bambuscms.wcontentlookup.fetch);
	}
});