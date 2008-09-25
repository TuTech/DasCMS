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
	var filecontainer = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-filecontainer"
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
	if(searchbox.addEventListener)
	{
		searchbox.addEventListener('keyup', org.bambuscms.wopenfiledialog.titleFilter, false);
		searchbox.addEventListener('mouseup', org.bambuscms.wopenfiledialog.titleFilter, false);
	}
	else if(searchbox.attachEvent)
	{
		searchbox.attachEvent('onkeyup', org.bambuscms.wopenfiledialog.titleFilter);
		searchbox.attachEvent('onmouseup', org.bambuscms.wopenfiledialog.titleFilter);
	}
	else
	{
		searchbox.onkeyup =  org.bambuscms.wopenfiledialog.titleFilter;		
		searchbox.onmouseup =  org.bambuscms.wopenfiledialog.titleFilter;
	}
	
	//tag box
	
	//info box
	
//header
	
	//view-switch
	var view_switch = document.createElement('div');
	view_switch.className = 'WSwitch';
	
	
	//sort-switch
	var sort_switch = document.createElement('div');
	sort_switch.className = 'WSwitch';
	
	
	//sort-dir-switch
	var sort_dir_switch = document.createElement('div');
	sort_dir_switch.className = 'WSwitch';
	

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

