org.bambuscms.wimagecontentlookup = {};
org.bambuscms.wimagecontentlookup.filter = function()
{
	org.bambuscms.wimagecontentlookup.fetch();
};
org.bambuscms.wimagecontentlookup.show = function()
{
	org.bambuscms.wimagecontentlookup.fetch();
};
org.bambuscms.wimagecontentlookup.hide = function(){};
org.bambuscms.wimagecontentlookup.generateList = function(respObject)
{
	if(respObject.items)
	{
		var html;
		if(!respObject.continueList)
		{
			html = $c('ul');
			html.id = 'wimagecontentlookup-list';
			$('View_UIElement_ImageContentLookup').innerHTML = '';
			$('View_UIElement_ImageContentLookup').appendChild(html);
			var inp = $c('input');
			inp.type = 'hidden';
			inp.id = "wimagecontentlookup-list-count";
			inp.value = 1;
			$('View_UIElement_ImageContentLookup').appendChild(inp);
			var lnk = $c('a');
			lnk.id = "wimagecontentlookup-list-more";
			lnk.href =  'javascript:org.bambuscms.wimagecontentlookup.fetch($(\'wimagecontentlookup-list-count\').value)';
			lnk.appendChild($t(_('more')));
			$('View_UIElement_ImageContentLookup').appendChild(lnk);
		}
		else
		{
			html = $('wimagecontentlookup-list');
			inp = $("wimagecontentlookup-list-count");
		}
		$("wimagecontentlookup-list-more").style.display = (respObject.hasMore) ? 'block' : 'none';
		var items = respObject.items;
		inp.value = parseInt(inp.value) + 1;		
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
org.bambuscms.wimagecontentlookup.fetch = function(more)
{
	var data = {
		'controller':'org.bambuscms.plugin.contentlookup',
		'call':'provideImageContentLookup'
	};
	var send = {
		'filter':'',
		'mode':'all'
	};
	if(more && typeof more != 'object')
	{
		send.page = more;
	}
	if($('View_UIElement_ImageContentLookupFilter'))
	{
		send.filter = $('View_UIElement_ImageContentLookupFilter').value;
	}
	if($('View_UIElement_ImageContentLookupMode'))
	{
		send.mode = $('View_UIElement_ImageContentLookupMode').options[$('View_UIElement_ImageContentLookupMode').selectedIndex].value;
	}
	send = org.json.stringify(send);
	var qobj = org.bambuscms.http.managementRequestURL(data);
	org.bambuscms.http.fetchJSONObject(
		qobj,
		org.bambuscms.wimagecontentlookup.generateList,
		send
	);
};

org.bambuscms.autorun.register(function(){
	if($('View_UIElement_ImageContentLookup'))
	{
		org.bambuscms.gui.setEventHandler($('View_UIElement_ImageContentLookupFilter'), 'keyup',   org.bambuscms.wimagecontentlookup.fetch);
		org.bambuscms.gui.setEventHandler($('View_UIElement_ImageContentLookupFilter'), 'mouseup', org.bambuscms.wimagecontentlookup.fetch);
	}
	if($('View_UIElement_ImageContentLookupMode'))
	{
		org.bambuscms.gui.setEventHandler($('View_UIElement_ImageContentLookupMode'), 'change', org.bambuscms.wimagecontentlookup.fetch);
	}
});

