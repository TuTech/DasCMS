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

function Delete()
{
	input = $c('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	org.bambuscms.app.dialog.create(_('delete_script'), _('do_you_really_want_to_delete_this_script'), input, _('yes_delete'), _('no'));
	org.bambuscms.app.dialog.setAction('delete');
}


org.bambuscms.app.document.insertMedia = function(type, url, title)
{
	var insert = '';
	switch(type)
	{
		case 'file':
			insert=' <a href="'+url+'" target="_blank">'+title+'</a> ';
			break;
		case 'image':
			insert='<img src="'+url+'" alt="'+title+'" title="'+title+'" />';
			break;
		case 'content':
			var view = prompt(_('target_view'), 'page');
			if(view)
				insert=' <a href="?'+view+'='+url+'">'+title+'</a> ';
			break;
	}
	if(insert != '')
	{
		//FIXME: check for Bespin
		org.bambuscms.app.document.insertText(insert);
	}
};