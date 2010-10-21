//show create dialog
org.bambuscms.app.document.create = function()
{
	input = $c('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	org.bambuscms.app.dialog.create(_('create_new_filtered_list'), _('name_of_new_filtered_list'), input, _('ok'), _('cancel'));
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
		
	org.bambuscms.app.dialog.create(_('delete_filtered_list'), _('do_you_really_want_to_delete_this_filtered_list'), input, _('yes'), _('no'));
	org.bambuscms.app.dialog.setAction('delete');
}
