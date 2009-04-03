//show create dialog
org.bambuscms.app.document.create = function()
{
	var input = $c('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
	
	var linput = $c('input');
	linput.setAttribute('name','content');
	linput.setAttribute('type','text');
	linput.setAttribute('value','');
	
	var box = $c('div');
	box.appendChild(input);
	//box.appendChild(linput),
	
	org.bambuscms.app.dialog.create(_('create_new_link'), _('link_of_title'), box, _('ok'), _('cancel'));
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

