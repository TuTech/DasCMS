function Upload()
{
	div = document.createElement('div');
	
	finput = document.createElement('input');
	finput.setAttribute('name','CFile');
	finput.setAttribute('type','file');
		
	minput = document.createElement('input');
	minput.setAttribute('name','MAX_FILE_SIZE');
	minput.setAttribute('type','hidden');
	minput.setAttribute('value','1000000000');
	
	cinput = document.createElement('input');
	cinput.setAttribute('name','bambus_overwrite_file');
	cinput.setAttribute('type','checkbox');
	cinput.setAttribute('id', 'dlgcheck');

	cdesc = document.createElement('label');
	cdesc.setAttribute('for', 'dlgcheck');
	cdesc.appendChild(document.createTextNode('overwrite'));

	br = document.createElement('br');

	div.appendChild(finput);
	div.appendChild(minput);
	div.appendChild(br);
	div.appendChild(cinput);
	div.appendChild(cdesc);
	
	DialogContainer('Upload file', 'file:', div, 'Upload', 'Cancel', true);
}
function Delete()
{
	input = document.createElement('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	DialogContainer('Delete website', 'Do you really want to delete this website', input, 'Yes', 'No');
}
/*****************************/
function selectImage(id)
{
	var image = document.getElementById(id);
	var select = document.getElementById('select_'+id);
	if(!select.checked)
	{
		image.style.background = org.bambuscms.app.primarySelectedObjectColor;
		select.checked = true;
	}
	else
	{
		image.style.background = '#fff';
		select.checked = false;
	}
}
function selectItems(allOrNone)
{
	var check, background;
	if(allOrNone)
	{
		check = true;
		background = org.bambuscms.app.primarySelectedObjectColor;
	}
	else
	{
		check = false;
		background = '#fff';
	}
	inputs = document.getElementsByTagName('input');
	var parent = '';
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].name.substr(0,7) == 'select_')
		{
			inputs[i].checked = check;
			parent = inputs[i].name;
			parent = parent.replace(/select_/, "");
			document.getElementById(parent).style.background = background;
		}
	}
}
function hideInputs()
{
	inputs = document.getElementsByTagName('input');
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].name.substr(0,7) == 'select_')
		{
			inputs[i].style.display = 'none';
			inputs[i].checked = false;
		}
		if(inputs[i].name == 'searchFilter')
		{
			inputs[i].value = '';
		}
	}
}
function downloadSelected(path)
{
	inputs = document.getElementsByTagName('input');
	if(!document.getElementById('downloadIFrames'))
	{
		document.getElementById("bambusJAX").innerHTML += '<div id="downloadIFrames" />';
	}
	else
	{
		document.getElementById('downloadIFrames').innerHTML = '';
	}
	var id;
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].name.substr(0,7) == 'select_' &&inputs[i].checked)
		{
			id = inputs[i].name.replace(/select_/, "");
			document.getElementById('downloadIFrames').innerHTML += '<iframe src="file.php?get='+id+'" class="downloadIFrame" />';
		}
	}	
}
