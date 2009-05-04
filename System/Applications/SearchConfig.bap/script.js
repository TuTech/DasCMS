//show create dialog
org.bambuscms.app.document.create = function()
{
	var input = $c('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	var content = $c('input');
	content.setAttribute('name','content');
	content.setAttribute('type','text');
	content.setAttribute('value','');
	
	var nlabel = $c('label');
	nlabel.appendChild($t(_('title')));
	
	var clabel = $c('label');
	clabel.appendChild($t(_('url')));
	
	
	var box = $c('div');
	var append = [nlabel, input];//, clabel, content
	for(var i = 0; i < append.length; i++)
	{
		box.appendChild(append[i]);
		box.appendChild($c('br'));
	}
	
	org.bambuscms.app.dialog.create(_('create_new_link'), '', box, _('ok'), _('cancel'));
	org.bambuscms.app.dialog.setAction('create');
	input.focus();
}
//show delete dialog
org.bambuscms.app.document.remove = function ()
{
	input = $c('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	org.bambuscms.app.dialog.create(_('delete_link'), _('do_you_really_want_to_delete_this_link'), input, _('yes_delete'), _('no_keep_link'));
	org.bambuscms.app.dialog.setAction('delete');
}

