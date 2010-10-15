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
			$('View_UIElement_ContentLookup').innerHTML = '';
			$('View_UIElement_ContentLookup').appendChild(html);
			var inp = $c('input');
			inp.type = 'hidden';
			inp.id = "wcontentlookup-list-count";
			inp.value = 1;
			$('View_UIElement_ContentLookup').appendChild(inp);
			var lnk = $c('a');
			lnk.id = "wcontentlookup-list-more";
			lnk.href =  'javascript:org.bambuscms.wcontentlookup.fetch($(\'wcontentlookup-list-count\').value)';
			lnk.appendChild($t(_('more')));
			$('View_UIElement_ContentLookup').appendChild(lnk);
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
			item.appendChild($t(items[i][1]));
			
			var pd = $c('span');
			var d = new Date(items[i][2]*1000);
			var text = (items[i][2] <= 0) ? _('not_public') : d.toUTCString();

			pd.appendChild($t(text));
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
	if(more && typeof more != 'object')
	{
		send.page = more;
	}
	if($('View_UIElement_ContentLookupFilter'))
	{
		send.filter = $('View_UIElement_ContentLookupFilter').value;
	}
	if($('View_UIElement_ContentLookupMode'))
	{
		send.mode = $('View_UIElement_ContentLookupMode').options[$('View_UIElement_ContentLookupMode').selectedIndex].value;
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
	if($('View_UIElement_ContentLookup'))
	{
		org.bambuscms.gui.setEventHandler($('View_UIElement_ContentLookupFilter'), 'keyup',   org.bambuscms.wcontentlookup.fetch);
		org.bambuscms.gui.setEventHandler($('View_UIElement_ContentLookupFilter'), 'mouseup', org.bambuscms.wcontentlookup.fetch);
	}
	if($('View_UIElement_ContentLookupMode'))
	{
		org.bambuscms.gui.setEventHandler($('View_UIElement_ContentLookupMode'), 'change', org.bambuscms.wcontentlookup.fetch);
	}
});