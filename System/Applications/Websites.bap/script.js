//show create dialog
org.bambuscms.app.document.create = function()
{
	input = document.createElement('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	org.bambuscms.app.dialog.create('Create new website', 'name of new website:', input, 'OK', 'Cancel');
	input.focus();
}
//show delete dialog
org.bambuscms.app.document.remove = function ()
{
	input = document.createElement('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	org.bambuscms.app.dialog.create('Delete website', 'Do you really want to delete this website', input, 'Yes', 'No');
}

