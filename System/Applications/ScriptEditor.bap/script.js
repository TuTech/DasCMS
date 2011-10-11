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
org.bambuscms.editor.wysiwyg.commitAll = function(){
	var ta = $('org_bambuscms_app_document_editorElementId');
	if(ta.bespin){
		ta.value = ta.bespin.editor.value;
	}
}


org.bambuscms.app.document.insertMedia = function(type, id, title)
{
	var insert = '';
};

