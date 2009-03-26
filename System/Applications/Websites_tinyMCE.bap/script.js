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
//var h = -190;//org_bambuscms_app_document_editorElementId_ifr
//if($(tinyMCE.get(id)).offsetTop)
//{
//	alert($(tinyMCE.get(id)).offsetTop);
//	h = function(){return ($(tinyMCE.get(id)).offsetTop+5)*-1;alert($(tinyMCE.get(id)).offsetTop)};
//}
//else
//{
//	alert('no top');
//}
//org.bambuscms.display.setAutosize(tinyMCE.get(id),0,h);

document.write('<script type="text/javascript" src="./System/External/tiny_mce/tiny_mce.js"></script>');
