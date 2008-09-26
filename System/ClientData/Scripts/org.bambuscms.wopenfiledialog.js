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
	'sortBy':'title',
	'sortOrder':'asc',
	'sort':function(by, order){
		if(by)
		{
			org.bambuscms.wopenfiledialog.sortBy = by;
		}
		if(order)
		{
			org.bambuscms.wopenfiledialog.sortOrder = order;
		}
		alert('sorting by '+org.bambuscms.wopenfiledialog.sortBy+' '+org.bambuscms.wopenfiledialog.sortOrder);
	},
	'titleFilter':function(){alert($('WOpenFileDialog-TitleSearch').value);}
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
	var filecontainer = org.bambuscms.gui.element('div', '<div id="WOpenFileDialog-InnerPadding"><div class="WOFD_item">test</div><div class="WOFD_item">1</div><div class="WOFD_item">2</div><div class="WOFD_item">3</div></div>', {
		'id':"WOpenFileDialog-filecontainer",
		'class':'WOFD_detail_view'
	});

//sidebar

	//search box
	var search_container = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-TitleSearchContainer"
	});	
	var search_title = org.bambuscms.gui.element('label', 'search by title', {
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
			{'title':"detail", 'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_detail_view';}}, 
			{'title':"icon",   'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_icon_view';}}, 
			{'title':"list",   'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_list_view';}}, 
		]
	); 
	header.appendChild(view_switch);

	//sort-switch
	var sort_switch = org.bambuscms.gui.switchButton(
		'sort',
		[
			{'title':"title", 'callBack': function(){org.bambuscms.wopenfiledialog.sort('title', null);}}, 
			{'title':"date",   'callBack': function(){org.bambuscms.wopenfiledialog.sort('date', null);}}, 
		]
	); 
	header.appendChild(sort_switch);

	//sort-dir-switch
	var sort_order_switch = org.bambuscms.gui.switchButton(
		'sort_order',
		[
			{'title':"ASC", 'callBack': function(){org.bambuscms.wopenfiledialog.sort(null, 'asc');}}, 
			{'title':"DESC",'callBack': function(){org.bambuscms.wopenfiledialog.sort(null, 'desc');}}, 
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
}

