function Create()
{
	input = $c('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	org.bambuscms.app.dialog.create(_('create_new_feed'), _('name_of_new_feed'), input, _('create'), _('cancel'));
	org.bambuscms.app.dialog.setAction('create');
	input.focus();
}
function Delete()
{
	input = $c('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	org.bambuscms.app.dialog.create(_('delete_feed'), _('do_you_really_want_to_delete_this_feed'), input, _('yes_delete_it'), _('no'));
	org.bambuscms.app.dialog.setAction('delete');
}

