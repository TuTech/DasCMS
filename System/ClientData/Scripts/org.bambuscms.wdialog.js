

org.bambuscms.wdialog = {
	"run":function(id)
	{
		if(org.bambuscms.wdialog.dialogs[id])
		{
			//init dialog
			org.bambuscms.wdialog.init();
			var src = org.bambuscms.wdialog.dialogs[id];
			var c = org.bambuscms.wdialog.container;
			var dialog = document.createElement('div');
			dialog.setAttribute('class', 'WDialog');
			var focusInput = null;
			
			//title
			var title = document.createElement('h2');
			title.appendChild(document.createTextNode(src.title));
			dialog.appendChild(title);
			
			//form
			var form = document.createElement('form');
			org.bambuscms.wdialog.form = form;
			form.setAttribute('action', $('documentform').getAttribute('action'));
			form.setAttribute('id', 'WDialog_form');
			form.setAttribute('method','post');
			if(src.isMultipart)
			{
				form.setAttribute('enctype','multipart/form-data');
			}
			dialog.appendChild(form);
			
			//sections
			if(src.sections && src.sections.length)
			{
				for(var i = 0; i < src.sections.length; i++)
				{
					var sect = src.sections[i];
					var block = document.createElement('div');
					block.setAttribute('id', 'WDialog_'+id+'_sect_'+i);
					block.setAttribute('class', 'WDialog_section');
					if(sect.title)
					{
						var btitle = document.createElement('h3');
						btitle.setAttribute('id', 'WDialog_'+id+'_sect_'+i+'_head');
						btitle.appendChild(document.createTextNode(sect.title));
						block.appendChild(btitle);
					}
					for(name in sect.items)
					{
						var obj = document.createElement('div');
						if(sect.items[name].title)
						{
							var otitle = document.createElement('label');
							otitle.setAttribute('for', 'WDialog_'+id+'_'+name);
							otitle.appendChild(document.createTextNode(sect.items[name].title));
							obj.appendChild(otitle);
							obj.setAttribute('class', 'WDialog_labeled');
						}
						
						//type,title,value
						switch(sect.items[name].type)
						{
							case 'text':
							case 'password':
							case 'file':
							case 'hidden':
							case 'checkbox':
								var input = document.createElement('input');
								input.setAttribute('type', sect.items[name].type);
								input.setAttribute('id', 'WDialog_'+id+'_'+name);
								input.setAttribute('name', name);
								if(sect.items[name].value)
								{
									if(sect.items[name].type != 'checkbox')
									{
										input.setAttribute('value', sect.items[name].value);
									}
									else
									{
										input.setAttribute('checked', 'checked');
									}
								}
								break;
							case 'choice':
							default:
								var input = document.createElement('span');
						}
						if(focusInput == null)
						{
							focusInput = input;
						}
						obj.appendChild(input);
						block.appendChild(obj);
					}
					form.appendChild(block);
				}
			}
			
			//buttons
			var buttons = [
				{"title":src.OK, 	"link":'org.bambuscms.wdialog.form.submit();'},
				{"title":src.Cancel,"link":'org.bambuscms.wdialog.cancel();'},
				{"title":src.Reset, "link":'org.bambuscms.wdialog.form.reset();'}
			];
			for(var i = 0; i < buttons.length; i++)
			{
				if(buttons[i].title)
				{
					var el = document.createElement('a');
					el.setAttribute('onclick', buttons[i].link+';return false;');
					el.setAttribute('href', '#');
					el.appendChild(document.createTextNode(buttons[i].title));
					dialog.appendChild(el);
				}
			}
			
			//finish
			var end = document.createElement('br');
			end.setAttribute('class', 'clear');
			dialog.appendChild(end);
			c.appendChild(dialog);
			
			//show
			c.style.display = 'block';
			
			if(focusInput != null)
			{
				focusInput.focus();
			}
		}
	},
	"init":function()
	{
		if(!org.bambuscms.wdialog.container)
		{
			org.bambuscms.wdialog.container = document.createElement('div');
			org.bambuscms.wdialog.container.setAttribute('id','WDialog_container');
			document.body.appendChild(org.bambuscms.wdialog.container);
		}
		else
		{
			while(org.bambuscms.wdialog.container.hasChildNodes())
			{
				var node = org.bambuscms.wdialog.container.firstChild;
				org.bambuscms.wdialog.container.removeChild(node);
			}
		}
		org.bambuscms.wdialog.container.style.display = 'none';;
	},
	"cancel":function()
	{
		if(org.bambuscms.wdialog.container)
		{
			org.bambuscms.wdialog.container.style.display = 'none';
		}
	},
	"dialogs":{},
	"conainer":null,
	"form":null
};
