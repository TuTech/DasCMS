org.bambuscms.app.dialog = {};
org.bambuscms.app.dialog.disableInput = false;
org.bambuscms.app.dialog.create = function(Ttitle, TDesc, Dcontent, confirmCaption, abortCaption, doMultipart, okFunctionName, cancelFunctionName)
{
	var container = document.getElementById("dialogues");
	doMultipart = (doMultipart == true);
	
	okFunctionName = (okFunctionName) ? okFunctionName :  'org.bambuscms.app.dialog.confirm';
	cancelFunctionName = (cancelFunctionName) ? cancelFunctionName :  'org.bambuscms.app.dialog.cancel';
	
	dialog = $c('div');
	dialog.setAttribute('class', 'dialogue');

	title = $c('h2');
	title.appendChild($t(Ttitle));
	dialog.appendChild(title);
	
	form = $c('form');
	form.setAttribute('action', document.getElementById('documentform').getAttribute('action'));
	form.setAttribute('id', 'dialogueform');
	form.setAttribute('method','post');
	if(doMultipart)
	{
		form.setAttribute('enctype','multipart/form-data');
	}
	desc = $c('p');
	desc.appendChild($t(TDesc));
	form.appendChild(desc);
	

	form.appendChild(Dcontent);
	
	dialog.appendChild(form);
	if(confirmCaption)
	{
		a_ok = $c('a');
		a_ok.setAttribute('href', 'javascript:'+okFunctionName+'();');
		a_ok.className = 'dialog_ok';
		a_ok.appendChild($t(confirmCaption));
		dialog.appendChild(a_ok);
	}
	if(abortCaption)
	{
		a_Cancel = $c('a');
		a_Cancel.className = 'dialog_cancel';
		a_Cancel.setAttribute('href', 'javascript:'+cancelFunctionName+'();');
		a_Cancel.appendChild($t(abortCaption));
		dialog.appendChild(a_Cancel);
	}
	end = $c('br');
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
	container.className = 'hide';
	container.innerHTML = '';
	while(container.hasChildNodes())
	{
		container.removeChild(container.firstChild);
	}
}
org.bambuscms.app.dialog.setAction = function(action)
{
	if($('dialogueform') && $('dialogueform').action)
	{
		if($('dialogueform').action.match(/_action=/))
		{
			$('dialogueform').action = $('dialogueform').action.replace(/_action=[a-zA-Z0-9%_-]*/, '_action='+escape(action));
		}
		else
		{
			$('dialogueform').action += '_action='+escape(action);
		}
	}
}


