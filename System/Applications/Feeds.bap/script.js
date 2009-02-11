function Create()
{
	input = document.createElement('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	org.bambuscms.app.dialog.create('Create new feed', 'name of new feed:', input, 'OK', 'Cancel');
	input.focus();
}
function Delete()
{
	input = document.createElement('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	org.bambuscms.app.dialog.create('Delete feed', 'Do you really want to delete this feed', input, 'Yes', 'No');
}

