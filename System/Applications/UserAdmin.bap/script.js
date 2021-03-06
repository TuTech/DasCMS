function addUser()
{ 
	document.getElementById('actionInput').value = 'create_new_user';
	document.getElementById('gcptg').value = '';
	document.getElementById('ucptg').value = 'edit';
	document.getElementById('addmode').value = 'usr';

	var toHide = document.getElementById('add_group_table');
	var toShow = document.getElementById('add_user_table');

	var toHL = document.getElementById('addUserLink');
	var toUL = document.getElementById('addGroupLink');
	
	toHL.className = 'activeAddButton';
	toUL.className = 'inactiveAddButton full';

	toHide.className = 'hide';
	toShow.className = 'borderedtable full';
}

function addGroup()
{
	document.getElementById('actionInput').value = 'create_new_group';
	document.getElementById('ucptg').value = '';
	document.getElementById('gcptg').value = 'edit';
	document.getElementById('addmode').value = 'grp';

	var toHide = document.getElementById('add_user_table');
	var toShow = document.getElementById('add_group_table');

	var toHL = document.getElementById('addGroupLink');
	var toUL = document.getElementById('addUserLink');
	
	toHL.className = 'activeAddButton';
	toUL.className = 'inactiveAddButton full';
	
	toHide.className = 'hide';
	toShow.className = 'borderedtable full';
}
