function insertMedia(type, url, title)
{
	var insert = '';
	switch(type)
	{
		case 'file':
			insert=(' <a href="'+url+'" target="_blank">'+title+'</a> ');
			break;
		case 'image':
			insert=(' <img src="'+url+'" alt="'+title+'" title="'+title+'" /> ');
			break;
	}
	org.bambuscms.app.document.insertText(insert);
}
function Create()
{
	input = document.createElement('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	DialogContainer('Create new template', 'name of new template:', input, 'OK', 'Cancel');
	input.focus();
}
function Delete()
{
	input = document.createElement('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	DialogContainer('Delete template', 'Do you really want to delete this template', input, 'Yes', 'No');
}

