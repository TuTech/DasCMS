org.bambuscms.wopenfiledialog = {
	'linkPrefix':'javascript:alert("',
	'linkSuffix':'");',
	'prepareLinks':function(prefix, suffix){
		org.bambuscms.wopenfiledialog.linkPrefix = prefix ? prefix : '';
		org.bambuscms.wopenfiledialog.linkSuffix = suffix ? suffix : '';
	},
	'dataSource':{},
	'setSource':function(src){
		if(typeof src == 'object')
		{
			org.bambuscms.wopenfiledialog.dataSource = src;
		}
	},
	'_build':function(){},
	'show':function()
	{
		org.bambuscms.wopenfiledialog._build();
		$('WOpenFileDialog-TitleSearch').focus();
	},
	'hide':function(){
		if($("WOpenFileDialog"))
		{
			document.body.removeChild($("WOpenFileDialog"));
		}
	},
	'toggle':function(){
		if($("WOpenFileDialog"))
		{
			org.bambuscms.wopenfiledialog.hide();
		}
		else
		{
			org.bambuscms.wopenfiledialog.show();
		}
	},
	'closable':true,
	'sortCompare': function(a, b){
		//a and b are dom objects
		var ca = a.firstChild.getAttribute(org.bambuscms.wopenfiledialog.sortBy);
		var cb = b.firstChild.getAttribute(org.bambuscms.wopenfiledialog.sortBy);
		var val = ca <= cb;
		return (org.bambuscms.wopenfiledialog.sortOrder) ? val : !val; 
	},
	'sortBy':'title',
	'sortOrder':true,
	'sort':function(by, order){
		if(by)
		{
			org.bambuscms.wopenfiledialog.sortBy = by;
		}
		if(order !== null)
		{
			org.bambuscms.wopenfiledialog.sortOrder = order == 'ASC';
		}
		var sorted;
		var bt = new org.bambuscms.bintree(org.bambuscms.wopenfiledialog.sortCompare);
		var wrapper = $("WOpenFileDialog-ItemWrapper");
		var items = wrapper.childNodes;
		for(var i = items.length-1; i >= 0; i--)
		{
			bt.add(items[i]);
			wrapper.removeChild(items[i]);
		}
		sorted = bt.toArray();
		for(var i = 0; i < sorted.length; i++)
		{
			wrapper.appendChild(sorted[i]);
		}
	},
	'titleFilter':function(){
		var lookFor = $('WOpenFileDialog-TitleSearch').value;
		var setKey = 'style';
		var setMatch = '';
		var setOther = (lookFor == '') ? '' : 'display:none;';
		var items = $("WOpenFileDialog-ItemWrapper").childNodes;
		
		lookFor = new RegExp(lookFor,'i');
		
		for(var i = 0; i < items.length; i++)
		{
			if(items[i].firstChild.getAttribute('title').match(lookFor))
			{
				items[i].removeAttribute('style');
			}
			else
			{
				items[i].setAttribute('style', 'display:none');
			}
		}
	},
	'openAlias':function(alias)
	{
		top.location.href = org.bambuscms.wopenfiledialog.linkPrefix + alias + org.bambuscms.wopenfiledialog.linkSuffix;
	}
};

org.bambuscms.wopenfiledialog._build = function()
{
	//basic layout
	var dialog = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog",
		'style':'display:none'
	});
	var closer = null;
	if(org.bambuscms.wopenfiledialog.closable) 
	{
		closer = org.bambuscms.gui.element('a', null, {
			'id':"WOpenFileDialog-closer",
			'href':'javascript:org.bambuscms.wopenfiledialog.hide();'
		});
	}
	var header = org.bambuscms.gui.element('div', closer, {
		'id':"WOpenFileDialog-header"
	});
	var sidebar = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-sidebar"
	});
	var filecontainer = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-filecontainer",
		'class':'WOFD_detail_view'
	});
	var filewrapper = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-ItemWrapper"
	});
	filecontainer.appendChild(filewrapper);

//SPLIT HERE	
	
	//fetch data
	var data = org.bambuscms.http.fetchJSONObject(org.bambuscms.wopenfiledialog.dataSource);
	
	//set title
	if(data.title)
	{
		var title = org.bambuscms.gui.element('h2', data.title, {});
		header.appendChild(title); 
	}
	
	if(data.nrOfItems == 0)
	{
		return;
	}
	
	//add items
	for(var y = 0; y < data.nrOfItems; y++)
	{
		//item
		var item = org.bambuscms.gui.element('a', null, {
			'class':"WOFD_item", 
			'href': org.bambuscms.wopenfiledialog.linkPrefix+data.items[y][data.itemMap['alias']]+org.bambuscms.wopenfiledialog.linkSuffix
		});
		//searchable attributes
		var search = org.bambuscms.gui.element('span', null, {'style':"display:none"});
		for(key in data.sortable)
		{
			search.setAttribute(key, data.items[y][data.itemMap[key]]);
		}
		item.appendChild(search);
		
		//display attributes
		if(data.iconMap[data.items[y][data.itemMap['icon']]])
		{
			item.appendChild(
				org.bambuscms.gui.element('img', null, {
					'src': data.smallIconMap[data.items[y][data.itemMap['icon']]],
					'class':'extra-small'
				})
			);
			item.appendChild(
				org.bambuscms.gui.element('img', null, {
					'src': data.iconMap[data.items[y][data.itemMap['icon']]],
					'class':'large'
				})
			);
		}		
		if(data.items[y][data.itemMap['title']])
		{
			item.appendChild(
				org.bambuscms.gui.element('h4', data.items[y][data.itemMap['title']], {})
			);
		}
		if(data.items[y][data.itemMap['description']])
		{
			item.appendChild(
				org.bambuscms.gui.element('div', data.captions.description+': '+data.items[y][data.itemMap['description']], {})
			);
		}
		if(data.items[y][data.itemMap['pubDate']])
		{
			if(data.items[y][data.itemMap['pubDate']] < 1)
			{
				item.appendChild(
					org.bambuscms.gui.element('div', data.captions.notPublished, {})
				);
			}
			else
			{
				var d = new Date(data.items[y][data.itemMap['pubDate']] * 1000);
				item.appendChild(
					org.bambuscms.gui.element('div', data.captions.pubDate+': '+d.toLocaleString(), {})
				);
			}
		}
		if(data.items[y][data.itemMap['modified']])
		{
			var d = new Date(data.items[y][data.itemMap['modified']] * 1000);
			item.appendChild(
				org.bambuscms.gui.element('div', data.captions.modified+': '+d.toLocaleString(), {})
			);
		}
		if(data.items[y][data.itemMap['size']])
		{
			var s = data.items[y][data.itemMap['size']];
			var u = ['Byte', 'KB', 'MB', 'GB', 'TB'];
			var i = 0;
			while(s > 1024)
			{
				i++;
				s = s/1024.0;
			}
			s = Math.round(s, 2);
			item.appendChild(
				org.bambuscms.gui.element('div', data.captions.size+': '+s+u[i], {})
			);
		}
		filewrapper.appendChild(item);
	}
	//sidebar

	//search box
	var search_container = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-TitleSearchContainer"
	});	
	var search_title = org.bambuscms.gui.element('label', data.captions.searchByTitle, {
		'for':"WOpenFileDialog-TitleSearch"
	});
	
	var searchbox = org.bambuscms.gui.element('input', null, {
		'id':"WOpenFileDialog-TitleSearch",
		'type':'text'
	});
	org.bambuscms.gui.setEventHandler(searchbox, 'keyup', org.bambuscms.wopenfiledialog.titleFilter);
	org.bambuscms.gui.setEventHandler(searchbox, 'mouseup', org.bambuscms.wopenfiledialog.titleFilter);
	//tag box
	
	//info box
	
//header
	
	//view-switch
	header.appendChild(org.bambuscms.gui.switchButton(
		'views',
		[
			{'title':data.captions.detail, 'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_detail_view';}}, 
			{'title':data.captions.icon,   'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_icon_view';}}, 
			{'title':data.captions.list,   'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_list_view';}}
		],
		'DetailIconList'
	));
	
	var sort_keys = [];
	for(sort_k in data.sortable)
	{
		sort_keys[sort_keys.length] =  {
			'title':data.captions[data.sortable[sort_k]], 
			'callBack': function(param){org.bambuscms.wopenfiledialog.sort(param, null);},
			'param':sort_k
		};
	}
	if(sort_keys.length > 1)
	{
		//sort-switch
		header.appendChild(org.bambuscms.gui.switchButton(
			'sort',
			sort_keys
		));
	}
	//sort-dir-switch
	header.appendChild(org.bambuscms.gui.switchButton(
		'sort_order',
		[
			{'title':data.captions.asc, 'callBack': function(){org.bambuscms.wopenfiledialog.sort(null, 'ASC');}}, 
			{'title':data.captions.desc,'callBack': function(){org.bambuscms.wopenfiledialog.sort(null, 'DESC');}}
		],
		'UpDown'
	));


	//link elements
	sidebar.appendChild(search_title);	
	search_container.appendChild(searchbox);
	sidebar.appendChild(search_container);	
	dialog.appendChild(sidebar);
	dialog.appendChild(header);
	dialog.appendChild(filecontainer);
	document.body.appendChild(dialog);
	org.bambuscms.display.setAutosize("WOpenFileDialog",0,-94, true);
	dialog.style.display = 'block';
	
	
	org.bambuscms.wopenfiledialog.sort(null, null);
}
org.bambuscms.wopenfiledialog.sortHelpFunction = function(sort)
{
	return function(){return sort;}
}
