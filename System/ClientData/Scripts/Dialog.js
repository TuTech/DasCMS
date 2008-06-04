var disableDialogInput = false;

function DialogContainer(Ttitle, TDesc, Dcontent, confirmCaption, abortCaption, doMultipart)
{
	var container = document.getElementById("dialogues");
	doMultipart = doMultipart!=undefined;
	
	dialogue = document.createElement('div');
	dialogue.setAttribute('class', 'dialogue');

	title = document.createElement('h2');
	title.appendChild(document.createTextNode(Ttitle));
	dialogue.appendChild(title);
	
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
	
	dialogue.appendChild(form);
	
	a_ok = document.createElement('a');
	a_ok.setAttribute('href', 'javascript:confirmDialog();');
	a_ok.appendChild(document.createTextNode(confirmCaption));

	a_Cancel = document.createElement('a');
	a_Cancel.setAttribute('href', 'javascript:closeDialog();');
	a_Cancel.appendChild(document.createTextNode(abortCaption));

	end = document.createElement('br');
	end.setAttribute('class', 'clear');

	dialogue.appendChild(a_ok);
	dialogue.appendChild(a_Cancel);
	dialogue.appendChild(end);
	while(container.hasChildNodes())
	{
		container.removeChild(container.firstChild);
	}
	container.appendChild(dialogue);
	container.className = 'show';
}
function confirmDialog()
{
	if(disableDialogInput)
		return;
	disableDialogInput = true;
	document.getElementById('dialogueform').submit();
}
function closeDialog()
{
	if(disableDialogInput)
		return;
	var container = document.getElementById("dialogues");
	while(container.hasChildNodes())
	{
		container.removeChild(container.firstChild);
	}
	container.className = 'hide';
}