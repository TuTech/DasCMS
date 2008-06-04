function CLASS_OpenFileDialog()
{
	this.initialized = false;
	
	
	this.openIcon = '';
	this.searchIcon = '';
	this.closeIcon = '';
	this.statusAnimation = '';
	
	this.openTranslation = 'open';
	this.closeTranslation = 'close';
	this.searchTranslation = 'search';
	this.categoriesTranslation = 'categories';
	this.groupsTranslation = 'groups';
	this.groupTitle = 'groups';
	
	this.self = 'OFD';
	
	this.infoText = 'Info';
	this.statusText = 'Status';
	
	this.groups = new Array();
	this.categrories = new Array();
	this.items = new Array();
	this.itemCategoryAssoc = new Array();
	this.itemGroupsAssoc = new Array();
	
	this.activeGroup = -1;
	
	this.headNode;
	
	this.getCategoryIdByName = function (name)
	{
		var i;
		for(i = 0; i < this.categrories.length; i++)
			if(this.categrories[i] == name) 
				return i;
		return -1;
	}
	
	this.getGroupIdByName = function (name)
	{
		var i;
		for(i = 0; i < this.groups.length; i++)
			if(this.groups[i] == name) 
				return i;
		return -1;
	}
	
	this.init = function ()
	{
		//init only once
		if(this.initialized)
			return;
		this.initialized = true;
		
		//read all available categories 
		//headline for different mimetypes (n:1)
		var catContainer;
		if(catContainer = document.getElementById('OFD_Categories'))
		{
			var catElems = catContainer.getElementsByTagName('span');
			for(var i = 0; i < catElems.length; i++)
				this.categrories[this.categrories.length] = catElems[i].innerHTML;
		}
		
		//read all available groups 
		//groups will be selectors left hand of the open dialog (n:n)
		var grpContainer;
		if(grpContainer = document.getElementById('OFD_Groups'))
		{
			var grpElems = grpContainer.getElementsByTagName('span');
			for(var i = 0; i < grpElems.length; i++)
				this.groups[this.groups.length] = grpElems[i].innerHTML;
		}
		
		//get items
		var itmContainer;
		if(itmContainer = document.getElementById('OFD_Items'))
		{
			//get items
			var itmElems = itmContainer.getElementsByTagName('a');
			var itemId, groupIds, title, link, description, icon, category, groups, temp, infos;
			
			//walk through items
			for(var i = 0; i < itmElems.length; i++)
			{
				//get item properties
				infos = itmElems[i].getElementsByTagName('span');
				
				link 		= itmElems[i].href;
				title 		= this.getTextFromElementsByAttributeValue(infos, 'title', 'title');
				description = this.getTextFromElementsByAttributeValue(infos, 'title', 'description');
				category	= this.getTextFromElementsByAttributeValue(infos, 'title', 'category');
				icon 		= this.getTextFromElementsByAttributeValue(infos, 'title', 'icon');
				
				groups = this.getEveryTextFromElementsByAttributeValue(infos, 'title', 'group');
				
				//create item entry
				itemId = this.items.length;
				this.items[itemId] = new Array(itemId, title, link, description, icon);
				
				//link item to groups and category
				this.itemCategoryAssoc[itemId] = this.getCategoryIdByName(category);
				groupIds = new Array();
				for(k = 0; k < groups.length; k++)
					groupIds[groupIds.length] = this.getGroupIdByName(groups[k]);
				this.itemGroupsAssoc[itemId] = groupIds;
			}
		}
		this.generateStaticHTML();
		RedrawAddId('OFD_Window', 10, 81+32-9);
		RedrawAddId('OFD_Body', ((this.groups.length > 0) ? 160 : 10)+1, 156);
		if(this.groups.length > 0)
			this.displayItems(0);
		Redraw();
	}
	
	this.filterItems = function (e)
	{
		var filter = document.getElementById('OFD_SearchInput').value.toLowerCase();
		var itmContainer = document.getElementById('OFD_Items');
		var items = itmContainer.getElementsByTagName('a');
		var hidden = -1;
		var title,attributes;
		var shown = 0;
		var lastFound = '';
		if(items.length > 0)
		{
			hidden = 0;
			for(var i = 0; i < items.length; i++)
			{
				if(
					document.getElementById('OFD_Item_'+i).style.display == 'none'
					&& document.getElementById('OFD_Item_'+i).style.visibility == 'hidden'
				)
				{
					continue;
				
				}
				title = '';
				attributes = items[i].getElementsByTagName('span');
				for(var f = 0; f < attributes.length; f++)
				{
					if(attributes[f].getAttribute('title') == 'title')
					{
						title = attributes[f].firstChild.nodeValue;
						break;
					}
				}
				if(filter == '' || title.toLowerCase().indexOf(filter) != -1)
				{
//					document.getElementById('OFD_Item_'+i).style.visibility = 'visible';
					document.getElementById('OFD_Item_'+i).style.display = 'block';
					lastFound = i;
					shown++;
				}
				else
				{
//					document.getElementById('OFD_Item_'+i).style.visibility = 'hidden';
					document.getElementById('OFD_Item_'+i).style.display = 'none';
					hidden++;
				}
			}
		}
		document.getElementById('OFD_SearchInput').setAttribute('title', '');
		document.getElementById('OFD_SearchInput').style.backgroundColor = '#ffffff';

		if(filter != '' && shown == 0 && hidden != 0)
			document.getElementById('OFD_SearchInput').style.backgroundColor = '#ef2929';
		else if(filter != '' && shown == 1)
		{
			document.getElementById('OFD_SearchInput').style.backgroundColor = '#8ae234';
			document.getElementById('OFD_SearchInput').setAttribute('title', lastFound);
		}
	}
	
	this.checkItemOpens = function (e)
	{
		if(e.which == 13)
		{
			var sa = document.getElementById('OFD_SearchInput').getAttribute('title');
			var sai = parseInt(sa);
			if(sa != '' && sai >= 0)
			{
				var itmContainer = document.getElementById('OFD_Items');
				var items = itmContainer.getElementsByTagName('a');
				if(items[sai])
				{
					top.location =items[sai].href;
//					alert('opening element '+sai+' --> '+items[sai].href);
				}
			}
		}
		document.getElementById('OFD_SearchInput').focus();

	}
	
	this.close = function()
	{
		if(document.getElementById('OFD_Window'))
			document.getElementById('OFD_Window').style.display = 'none';
	}
	
	this.show = function ()
	{
		if(!this.initialized)
			this.init();
		document.getElementById('OFD_Window').style.display = 'block';
		document.getElementById('OFD_SearchInput').focus();
		
	}
		
	this.toggle = function()
	{
		if(document.getElementById('OFD_Window') && document.getElementById('OFD_Window').style.display == 'block')
		{
			this.close();
		}
		else
		{
			this.show();
		}
		
	}

	this.displayItems = function (inGroup)
	{
		//group item onclick:
		//start loading ani
		document.getElementById('OFD_Statusbox').style.visibility = 'visible';
		if(inGroup != this.activeGroup)
		{
			document.getElementById('OFD_Group_'+(Math.max(0,this.activeGroup))).className = '';
			document.getElementById('OFD_Group_'+inGroup).className = 'active';
			this.activeGroup = inGroup;
			
			//set all items to display:none
			var itmContainer = document.getElementById('OFD_Items');
			var items = itmContainer.getElementsByTagName('a');
			
			for(var i = 0; i < items.length; i++)
			{
				document.getElementById('OFD_Item_'+i).style.display = 'none';
				document.getElementById('OFD_Item_'+i).style.visibility = 'hidden';
			}
			for(var i = 0; i < items.length; i++)
			{
				//this.itemGroupsAssoc[itemId][] = groupId;
				for(var a = 0; a < this.itemGroupsAssoc[i].length; a++)
				{
					if(this.itemGroupsAssoc[i][a] == inGroup)
					{
						document.getElementById('OFD_Item_'+i).style.display = 'block';
						document.getElementById('OFD_Item_'+i).style.visibility = 'visible';
						break;
					}
				}
			}

			//re-search
			this.filterItems('');
		}
		document.getElementById('OFD_Statusbox').style.visibility = 'hidden';
	}

	this.generateStaticHTML = function()
	{
		//generate static html stuff
		
		//////////////////
		//the main frame//
		//////////////////
		//mf:
		var window = document.createElement('div');
		window.setAttribute('id', 'OFD_Window');
		window.style.display = 'none';
			/////////////////
			//the head part//
			/////////////////
			//mf->head:
			var header = document.createElement('div');
			header.setAttribute('id', 'OFD_Header');		
	
				//the "close" link containing icon and caption
				//mf->head->il_container:
				var iconTitleLink  = document.createElement('div');
				iconTitleLink.setAttribute('id', 'OFD_IconAndTitle');		
				
					//the icon 
					//mf->head->il_container->icon:
					var icon  = document.createElement('img');
					icon.setAttribute('src', this.openIcon);
					icon.setAttribute('alt', '');
					icon.onclick = this.close
					
					//the icon 
					//mf->head->il_container->text:
					var text  = document.createElement('h2');
					var textContent = document.createTextNode(this.openTranslation);
					text.appendChild(textContent);
				
				iconTitleLink.appendChild(icon);
				iconTitleLink.appendChild(text);
				
				//the search field 
				//mf->head->search
				var searchBox = document.createElement('div');
				searchBox.setAttribute('id', 'OFD_Search');		

					//the search input
					//mf->head->search->searchInput
					var searchInput = document.createElement('input');
					searchInput.setAttribute('id', 'OFD_SearchInput');	
					searchInput.setAttribute('type', 'text');	
					searchInput.onkeyup = this.filterItems;
					searchInput.onkeypress = this.checkItemOpens;
				
				searchBox.appendChild(searchInput);
					
			header.appendChild(iconTitleLink);
			header.appendChild(searchBox);
			
			//////////
			//groups//
			//////////
			
			var groupbox,
				grouplink,
				groupname;
			groupbox = document.createElement('div');
			groupbox.setAttribute('id', 'OFD_GroupBox');	
			
			for(var g = 0; g < this.groups.length; g++)
			{
				grouplink = document.createElement('a');
				if(g == 0)
					grouplink.setAttribute('class', 'active');
				grouplink.setAttribute('id', 'OFD_Group_'+g);
				grouplink.setAttribute('href', 'javascript:{'+this.self+'.displayItems(\''+g+'\');}')
				
				groupname = document.createTextNode(this.groups[g]);
				grouplink.appendChild(groupname);
				
				groupbox.appendChild(grouplink);
				
			}
			window.appendChild(groupbox);
			
			////////////
			//the body//
			////////////
			//mf->body
			var body = document.createElement('div');
			body.setAttribute('id', 'OFD_Body');	
			var ttl, 
				pnode, ptextnode, 
				itemnode, itemtextnode, 
				itemiconbox, 
					itemicon, 
				iteminfobox, 
					itemtitle, itemtitletext, 
					itemdescription, itemdescriptiontext;
			
			//categories
			if(this.categrories.length == 0)
			{
				this.categrories[0] = '';
			}
			for(var c = 0; c < this.categrories.length; c++)
			{
				//print cat name
				pnode = document.createElement('p');
				pnode.setAttribute('class', 'OFD_CategoryTitle');		
				
				ptextnode = document.createTextNode(this.categrories[c]);
				pnode.appendChild(ptextnode);
				
				ttl = false;
				
				for(var it = 0; it < this.items.length; it++)
				{
					if(this.itemCategoryAssoc[it] == c)
					{
						//do not show empty categories 
						if(!ttl)
						{
							ttl = true;
							body.appendChild(pnode);
						}
						itemnode = document.createElement('div');
						itemnode.setAttribute('class', 'OFD_Item');	
						itemnode.setAttribute('id', 'OFD_Item_'+it);	
						
							//itemiconbox = document.createElement('div');
							//itemiconbox.setAttribute('class', 'OFD_ItemIconBox');	
							
							
							//itemiconbox.appendChild(itemicon);
						
							iteminfobox = document.createElement('a');
							iteminfobox.setAttribute('class', 'OFD_ItemInfoBox');	
							iteminfobox.setAttribute('href', this.items[it][2]);	
							
								itemtitle = document.createElement('b');
								itemtitletext = document.createTextNode(this.items[it][1]);
								itemtitle.appendChild(itemtitletext);	
								
								itemdescription = document.createElement('i');
								itemdescriptiontext = document.createTextNode(this.items[it][3]);
								itemdescription.appendChild(itemdescriptiontext);	
	
								itemicon = document.createElement('img');
								itemicon.setAttribute('src', this.items[it][4]);	
								itemicon.setAttribute('alt', this.items[it][4]);	

							iteminfobox.appendChild(itemicon);
							iteminfobox.appendChild(itemtitle);	
							iteminfobox.appendChild(itemdescription);	
							
						//itemnode.appendChild(itemiconbox);	
						itemnode.appendChild(iteminfobox);	
						
						body.appendChild(itemnode);
					}
				}
			}

			//////////////
			//the footer//
			//////////////
			//mf->footer
			var footer = document.createElement('div');
			footer.setAttribute('id', 'OFD_Footer');	
			
				//mf->footer->infobox
				var infobox  = document.createElement('span');
				infobox.setAttribute('id', 'OFD_Infobox');	
				var infoboxContent = document.createTextNode(this.infoText);
				infobox.appendChild(infoboxContent);
				
				//mf->footer->statusbox
				var statusbox  = document.createElement('span');
				statusbox.setAttribute('id', 'OFD_Statusbox');	
				
					//mf->footer->statusbox->text
					var statustext  = document.createElement('span');
					statustext.setAttribute('id', 'OFD_StatusMessage');	
					var statusContent = document.createTextNode(this.statusText);
					statustext.appendChild(statusContent);
					
					//mf->footer->statusbox->statusAnimation
					var statusAnimation  = document.createElement('img');
					statusAnimation.setAttribute('src', this.statusAnimation);
					statusAnimation.setAttribute('alt', '');
					
				statusbox.appendChild(statustext);
				statusbox.appendChild(statusAnimation);
			
			footer.appendChild(infobox);
			footer.appendChild(statusbox);
				
		///////////////////
		//generate window//
		///////////////////
			
		window.appendChild(header);
		window.appendChild(body);
		window.appendChild(footer);
		
		//add header to html tree
		document.getElementById('bambusJAX').appendChild(window);
	}
	
	this.getTextFromElementsByAttributeValue = function (elements, attribute, value)
	{
		var ret = '';
		var attval;
		for(var i = 0; i < elements.length; i++)
		{
			if(elements[i].getAttribute(attribute) == value)
				ret = elements[i].firstChild.nodeValue;
		}
		return ret;
	}
	
	this.getEveryTextFromElementsByAttributeValue = function (elements, attribute, value)
	{
		var values = new Array();
		var attval;
		for(var i = 0; i < elements.length; i++)
		{
			if(elements[i].getAttribute(attribute) == value)
				values[values.length] = elements[i].firstChild.nodeValue;
		}
		return values;
	}
	
	this.debug = function ()
	{
		alert('Groups: '+this.groups.join(', '));
		alert('Categories: '+this.categrories.join(', '));
		alert('Items: '+this.items.join('\n'));
		alert('Item Cat rel: '+this.itemCategoryAssoc.join('\n'));
		alert('Item Grp rel: '+this.itemGroupsAssoc.join('\n'));
		
	}
}