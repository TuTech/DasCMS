function Create()
{
	input = $c('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	org.bambuscms.app.dialog.create(_('create_new_script'), _('filename'), input, _('create'), _('cancel'));
	org.bambuscms.app.dialog.setAction('create');
	input.focus();
}



org.bambuscms.app.document.insertMedia = function(type, id, title)
{
	var insert = '';
};

