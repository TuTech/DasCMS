org.bambuscms.app.dialog = {};
org.bambuscms.app.dialog.disableInput = false;
org.bambuscms.app.dialog.create = function(Ttitle, TDesc, Dcontent, confirmCaption, abortCaption, doMultipart)
{
	var container = document.getElementById("dialogues");
	doMultipart = doMultipart!=undefined;
	
	dialog = document.createElement('div');
	dialog.setAttribute('class', 'dialogue');

	title = document.createElement('h2');
	title.appendChild(document.createTextNode(Ttitle));
	dialog.appendChild(title);
	
	form = document.createElement('form');
	form.setAttribute('action', document.getElementById('documentform').getAttribute('action'));
	form.setAttribute('id', 'dialogueform');
	form.setAttribute('method','post');
	if(doMultipart)
	{
		form.setAttribute('enctype','multipart/form-data');
	}
	desc = document.createElement('p');
	desc.appendChild(document.createTextNode(TDesc));
	form.appendChild(desc);
	

	form.appendChild(Dcontent);
	
	dialog.appendChild(form);
	if(confirmCaption)
	{
		a_ok = document.createElement('a');
		a_ok.setAttribute('href', 'javascript:org.bambuscms.app.dialog.confirm();');
		a_ok.className = 'dialog_ok';
		a_ok.appendChild(document.createTextNode(confirmCaption));
		dialog.appendChild(a_ok);
	}
	if(abortCaption)
	{
		a_Cancel = document.createElement('a');
		a_Cancel.className = 'dialog_cancel';
		a_Cancel.setAttribute('href', 'javascript:org.bambuscms.app.dialog.cancel();');
		a_Cancel.appendChild(document.createTextNode(abortCaption));
		dialog.appendChild(a_Cancel);
	}
	end = document.createElement('br');
	end.setAttribute('class', 'clear');

	dialog.appendChild(end);
	while(container.hasChildNodes())
	{
		container.removeChild(container.firstChild);
	}
	container.appendChild(dialog);
	container.className = 'show';
	return dialog;
}
org.bambuscms.app.dialog.confirm = function()
{
	if(org.bambuscms.app.dialog.disableInput)
		return;
	org.bambuscms.app.dialog.disableInput = true;
	document.getElementById('dialogueform').submit();
}
org.bambuscms.app.dialog.cancel = function()
{
	if(org.bambuscms.app.dialog.disableInput)
		return;
	var container = document.getElementById("dialogues");
	while(container.hasChildNodes())
	{
		container.removeChild(container.firstChild);
	}
	container.className = 'hide';
}


