//show create dialog
org.bambuscms.app.document.create = function()
{
	input = $c('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	org.bambuscms.app.dialog.create(_('create_new_website'), _('name_of_new_website'), input, _('ok'), _('cancel'));
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
		
	org.bambuscms.app.dialog.create(_('delete_website'), _('do_you_really_want_to_delete_this_website'), input, _('yes'), _('no'));
	org.bambuscms.app.dialog.setAction('delete');
}



var id = 'org_bambuscms_app_document_editorElementId';

document.write('<script type="text/javascript" src="./System/External/tiny_mce/tiny_mce.js"></script>');
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
		if(tinyMCE){
			tinyMCE.execInstanceCommand(id, 'mceInsertContent', false,insert);
		}
		else{
			org.bambuscms.app.document.insertText(insert);
		}
	}
};

