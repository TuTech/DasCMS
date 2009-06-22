org.bambuscms.wopenfiledialog = {
	'shown':function(){return ($("WOpenFileDialog")) ? true : false;/*map to bool*/},
	'linkPrefix':'javascript:alert("',
	'linkSuffix':'");',
	'prepareLinks':function(prefix, suffix){
		org.bambuscms.wopenfiledialog.linkPrefix = prefix ? prefix : '';
		org.bambuscms.wopenfiledialog.linkSuffix = suffix ? suffix : '';
	},
	'dataSource':null,
	'setSource':function(src){
		if(typeof src == 'object')
		{
			org.bambuscms.wopenfiledialog.dataSource = src;
		}
	},
	'_build':function(){},
	'_show':function()
	{
		org.bambuscms.wopenfiledialog._build();
		if($('WOpenFileDialog-TitleSearch'))
		{
			$('WOpenFileDialog-TitleSearch').focus();
		}
	},
	'show':function()
	{
		window.setTimeout('org.bambuscms.wopenfiledialog._show()', 1);
	},
	'hide':function(){
		if($("WOpenFileDialog") && org.bambuscms.wopenfiledialog.closable)
		{
			document.body.removeChild($("WOpenFileDialog"));
			return true;//closed
		}
		return false;
	},
	'toggle':function(){
		if($("WOpenFileDialog"))
		{
			return !org.bambuscms.wopenfiledialog.hide();
		}
		else
		{
			org.bambuscms.wopenfiledialog.show();
			return true;
		}
	},
	'closable':true,
	'extraSmallPreviewImageScale':'10-10-1-f-ff-ff-ff',
	'largePreviewImageScale':'30-30-1-f-ff-ff-ff',
	'extraLargePreviewImageScale':'B2-B2-1-f-ff-ff-ff',
	'showExtraLarge':function(itemNr){
		var cn = $('_item_'+itemNr).className;
		$('WOpenFileDialog-SidebarPreview').src = 'image.php/'+($('_item_'+itemNr).title)+'/'+org.bambuscms.wopenfiledialog.extraLargePreviewImageScale;
		$('WOpenFileDialog-SidebarPreview').className = '';
		var links = $("WOpenFileDialog-ItemWrapper").getElementsByTagName('a');
		for(var i = 0; i < links.length; i++)
		{
			links[i].className = 'WOFD_item';
		}
		$('_item_'+itemNr).className = 'WOFD_item WOFD_item_current';
		var openLink = (cn.match("_current") != null);
		if(!openLink)
		{
			var qobj = org.bambuscms.http.managementRequestURL({
				'controller':org.bambuscms.app.controller,
				'call':'provideContentTags'
			});
			org.bambuscms.http.fetchJSONObject(
				qobj,
				org.bambuscms.wopenfiledialog.setContentTags,
				org.json.stringify({'alias':$('_item_'+itemNr).title})
			);
		}
		return openLink;
	},
	'hideExtraLarge':function(itemNr){
	},
	'setContentTags':function(reqObj){
		var it = $('WOpenFileDialog-ItemTags');
		it.innerHTML = '';
		if(reqObj && reqObj.tags && typeof reqObj.tags == 'object')
		{
			var txt = reqObj.tags.join(', ');
			if(txt != '')
			{
				var d = $c('div');
				d.appendChild($t(txt));
				var l= $c('label');
				l.appendChild($t(_('tags')));
				it.appendChild(l);
				it.appendChild(d);
			}
		}
	},
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
org.bambuscms.wopenfiledialog._bodyFrame = null;
org.bambuscms.wopenfiledialog._contentFrame = null;
org.bambuscms.wopenfiledialog._headerFrame = null;
org.bambuscms.wopenfiledialog._loadContent= function(data)
{
	//set title
	if(data.title)
	{
		var title = org.bambuscms.gui.element('h2', data.title, {});
		org.bambuscms.wopenfiledialog._headerFrame.appendChild(title); 
	}
	
	if(data.nrOfItems > 0)
	{
		//add items
		for(var y = 0; y < data.nrOfItems; y++)
		{
			//item
			var item = org.bambuscms.gui.element('a', null, {
				'class':"WOFD_item", 
				'href': org.bambuscms.wopenfiledialog.linkPrefix+data.items[y][data.itemMap['alias']]+org.bambuscms.wopenfiledialog.linkSuffix,
				'title':data.items[y][data.itemMap['alias']],
				'id':'_item_'+y,
				'onclick':'return org.bambuscms.wopenfiledialog.showExtraLarge('+y+');',
				'ondblclick':'top.location.href = this.href;'
				//'onmouseout': 'org.bambuscms.wopenfiledialog.hideExtraLarge('+y+')'
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
			else
			{
				//show preview image
				item.appendChild(
					org.bambuscms.gui.element('img', null, {
							'src': 'image.php/'+data.items[y][data.itemMap['alias']]+'/'+
										org.bambuscms.wopenfiledialog.extraSmallPreviewImageScale,
							'class':'extra-small'
						})
					);
				item.appendChild(
						org.bambuscms.gui.element('img', null, {
							'src': 'image.php/'+data.items[y][data.itemMap['alias']]+'/'+
										org.bambuscms.wopenfiledialog.largePreviewImageScale,
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
					org.bambuscms.gui.element('div', _('description')+': '+data.items[y][data.itemMap['description']], {})
				);
			}
			if(data.items[y][data.itemMap['company']])
			{
				item.appendChild(
					org.bambuscms.gui.element('div', _('company')+': '+data.items[y][data.itemMap['company']], {})
				);
			}
			if(data.items[y][data.itemMap['pubDate']])
			{
				if(data.items[y][data.itemMap['pubDate']] < 1)
				{
					item.appendChild(
						org.bambuscms.gui.element('div', _('not_published'), {})
					);
				}
				else
				{
					var d = new Date(data.items[y][data.itemMap['pubDate']] * 1000);
					item.appendChild(
						org.bambuscms.gui.element('div', _('pubDate')+': '+d.toLocaleString(), {})
					);
				}
			}
			if(data.items[y][data.itemMap['modified']])
			{
				var d = new Date(data.items[y][data.itemMap['modified']] * 1000);
				item.appendChild(
					org.bambuscms.gui.element('div', _('modified')+': '+d.toLocaleString(), {})
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
					org.bambuscms.gui.element('div', _('size')+': '+s+u[i], {})
				);
			}
			org.bambuscms.wopenfiledialog._contentFrame.appendChild(item);
		}
		
		//header controls
		var sort_keys = [];
		for(sort_k in data.sortable)
		{
			sort_keys[sort_keys.length] =  {
				'title':_(data.sortable[sort_k]), 
				'callBack': function(param){org.bambuscms.wopenfiledialog.sort(param, null);},
				'param':sort_k
			};
		}
		if(sort_keys.length > 1)
		{
			//sort-switch
			org.bambuscms.wopenfiledialog._headerFrame.appendChild(org.bambuscms.gui.switchButton(
				'sort',
				sort_keys
			));
		}
		//sort-dir-switch
		org.bambuscms.wopenfiledialog._headerFrame.appendChild(org.bambuscms.gui.switchButton(
			'sort_order',
			[
				{'title':_('asc'), 'callBack': function(){org.bambuscms.wopenfiledialog.sort(null, 'ASC');}}, 
				{'title':_('desc'),'callBack': function(){org.bambuscms.wopenfiledialog.sort(null, 'DESC');}}
			],
			'UpDown'
		));
	}
	//show items
	org.bambuscms.wopenfiledialog._contentFrame.className = '';
	org.bambuscms.wopenfiledialog._bodyFrame.className = 'WOFD_detail_view';
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
			'href':'javascript:(function(){org.bambuscms.wopenfiledialog.hide();})();'
		});
	}
	var header = org.bambuscms.gui.element('div', closer, {
		'id':"WOpenFileDialog-header"
	});
	org.bambuscms.wopenfiledialog._headerFrame = header;
	var sidebar = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-sidebar"
	});
	sidebar.appendChild(
		org.bambuscms.gui.element('div', null, {
			'id':"WOpenFileDialog-sidebarSpacer"
		})
	);
	var filecontainer = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-filecontainer",
		'class':'WOFD_detail_view WOpenFileDialog-loading'
	});
	org.bambuscms.wopenfiledialog._bodyFrame = filecontainer;
	var filewrapper = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-ItemWrapper",
		'class':'WOpenFileDialog-loading'
	});
	org.bambuscms.wopenfiledialog._contentFrame = filewrapper;
	filecontainer.appendChild(filewrapper);

//SPLIT HERE	
	if(org.bambuscms.wopenfiledialog.dataSource == null)
	{
		org.bambuscms.wopenfiledialog.dataSource = {
			'controller':org.bambuscms.app.controller,
			'call':'provideOpenDialogData'
		};
	}
	
	org.bambuscms.http.fetchJSONObject(org.bambuscms.wopenfiledialog.dataSource, org.bambuscms.wopenfiledialog._loadContent);
	
	//search box
	var preview_image = org.bambuscms.gui.element('img', null, {
		'id':"WOpenFileDialog-SidebarPreview",
		'class':'_no_preview'
		
	});	
	var preview_image_frame = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-SidebarPreviewFrame"
	});	
	var preview_image_title = $c('label');
	preview_image_title.appendChild($t(_('preview_image')));
	var item_tags = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-ItemTags"
	});	
	preview_image_frame.appendChild(preview_image);
	var search_container = org.bambuscms.gui.element('div', null, {
		'id':"WOpenFileDialog-TitleSearchContainer"
	});	
	var search_title = org.bambuscms.gui.element('label', _('search_by_title'), {
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
			{'title':_('detail'), 'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_detail_view';}}, 
			{'title':_('icon'),   'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_icon_view';}}, 
			{'title':_('list'),   'callBack': function(){$('WOpenFileDialog-filecontainer').className = 'WOFD_list_view';}}
		],
		'DetailIconList'
	));
	
	//link elements
	sidebar.appendChild(search_title);	
	search_container.appendChild(searchbox);
	sidebar.appendChild(search_container);	
	sidebar.appendChild(preview_image_title);
	sidebar.appendChild(preview_image_frame);	
	sidebar.appendChild(item_tags);	
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
