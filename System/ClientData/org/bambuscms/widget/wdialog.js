

org.bambuscms.wdialog = {
	"run":function(id)
	{
		if(org.bambuscms.wdialog.dialogs[id])
		{
			//init dialog
			org.bambuscms.wdialog.init();
			var src = org.bambuscms.wdialog.dialogs[id];
			var c = org.bambuscms.wdialog.container;
			var dialog = org.bambuscms.gui.element('div', null, {
				'class':"WDialog"
			});
			var focusInput = null;
			
			//title
			var title = org.bambuscms.gui.element('h2', src.title, {});
			dialog.appendChild(title);
			
			//form
			var form = org.bambuscms.gui.element('form', null, {
				'action':$('documentform').getAttribute('action'),
				'id':'WDialog_form',
				'method':'post',
			});
			if(src.isMultipart)
			{
				form.setAttribute('enctype','multipart/form-data');
			}
			org.bambuscms.wdialog.form = form;
			dialog.appendChild(form);
			
			//sections
			if(src.sections && src.sections.length)
			{
				for(var i = 0; i < src.sections.length; i++)
				{
					var sect = src.sections[i];
					var block = org.bambuscms.gui.element('div', null, {
						'id':'WDialog_'+id+'_sect_'+i,
						'class': 'WDialog_section'
					});
					if(sect.title)
					{
						var btitle = org.bambuscms.gui.element('h3', sect.title, {
							'id':'WDialog_'+id+'_sect_'+i+'_head'
						});
						block.appendChild(btitle);
					}
					for(name in sect.items)
					{
						var obj = $c('div');
						if(sect.items[name].title)
						{
							var otitle = $c('label');
							otitle.setAttribute('for', 'WDialog_'+id+'_'+name);
							otitle.appendChild($t(sect.items[name].title));
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
								var input = org.bambuscms.gui.element('input', sect.title, {
									'id':'WDialog_'+id+'_'+name,
									'type': sect.items[name].type,
									'name': name
								});
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
								var input = $c('span');
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
					var el = org.bambuscms.gui.element('a', buttons[i].title, {
						'onclick': buttons[i].link+';return false;',
						'href': '#',
						'name': name
					});
					dialog.appendChild(el);
				}
			}
			
			//finish
			var end = $c('br');
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
			org.bambuscms.wdialog.container = $c('div');
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
