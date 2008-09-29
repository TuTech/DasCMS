org.bambuscms.wopenfiledialog = {
	'dataSource':null,
	'sourceType':null,
	'setSource':function(src){
		if(typeof src == 'object')
		{
			org.bambuscms.wopenfiledialog.dataSource = org.bambuscms.http.managementRequestURL(src);
			org.bambuscms.wopenfiledialog.dataSource = 'url';
		}
		else
		{
			org.bambuscms.wopenfiledialog.dataSource = src;
			org.bambuscms.wopenfiledialog.dataSource = 'embed';
		}
	},
	'_build':function(){},
	'show':function(){},
	'hide':function(){},
	'toggle':function(){},
	'isVisible':false,
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
	}
};

org.bambuscms.wopenfiledialog._build = function()
{
	//basic layout
	var dialog = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog",
		'style':'display:none'
	});
	var header = org.bambuscms.gui.element('div', null, {
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
	
	//fetch data
	var data = org.bambuscms.http.fetchJSONObject({'_OpenFiles':'MError'});
	
	//set title
	if(data.title)
	{
		var title = org.bambuscms.gui.element('h2', data.title, {});
		header.appendChild(title); 
	}
	
	//add items
	for(var y = 0; y < data.nrOfItems; y++)
	{
		//item
		var item = org.bambuscms.gui.element('a', null, {
			'class':"WOFD_item", 
			'href': data.linkPrefix+data.items[y][data.itemMap['alias']]+data.linkSuffix
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
				org.bambuscms.gui.element('img', null, {'src': data.iconMap[data.items[y][data.itemMap['icon']]]})
			);
		}		
		if(data.items[y][data.itemMap['title']])
		{
			item.appendChild(
				org.bambuscms.gui.element('h4', data.items[y][data.itemMap['title']], {})
			);
		}
		if(data.items[y][data.itemMap['pubDate']])
		{
			var d = new Date(data.items[y][data.itemMap['pubDate']] * 1000);
			item.appendChild(
				org.bambuscms.gui.element('div', data.captions.pubDate+': '+d.toLocaleString(), {})
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
	var view_switch = org.bambuscms.gui.switchButton(
		'views',
		[
			{'title':data.captions.detail, 'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_detail_view';}}, 
			{'title':data.captions.icon,   'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_icon_view';}}, 
			{'title':data.captions.list,   'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_list_view';}}, 
		]
	); 
	header.appendChild(view_switch);

	var keys = [];
	for(key in data.sortable)
	{
		var k = {
			'title':data.captions[data.sortable[key]], 
			'callBack': function(){org.bambuscms.wopenfiledialog.sort(key, null);}
		};
		keys[keys.length] = k;
	}
	//sort-switch
	var sort_switch = org.bambuscms.gui.switchButton(
		'sort',
		keys
	); 
	header.appendChild(sort_switch);

	//sort-dir-switch
	var sort_order_switch = org.bambuscms.gui.switchButton(
		'sort_order',
		[
			{'title':data.captions.asc, 'callBack': function(){org.bambuscms.wopenfiledialog.sort(null, 'ASC');}}, 
			{'title':data.captions.desc,'callBack': function(){org.bambuscms.wopenfiledialog.sort(null, 'DESC');}}, 
		]
	); 
	header.appendChild(sort_order_switch);

	
	

//file container

	//link elements
	sidebar.appendChild(search_title);	
	search_container.appendChild(searchbox);
	sidebar.appendChild(search_container);	
	dialog.appendChild(sidebar);
	dialog.appendChild(header);
	dialog.appendChild(filecontainer);
	dialog.style.display = 'block';
	document.body.appendChild(dialog);
	
	org.bambuscms.wopenfiledialog.sort(null, null);
}

