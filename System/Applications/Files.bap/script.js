function updateFolderView()
{
	var data = {
		'controller':'org.bambuscms.applications.files',
		'call':'getFolders'
	};
	$('folderView').innerHTML = '';
	$('folderViewFrame').className += ' waitingForData';
	updateFileView(-1);
	var qobj = org.bambuscms.http.managementRequestURL(data);
	org.bambuscms.http.fetchJSONObject(
		qobj,
		drawFolderView
	);
}

function drawFolderView(dataObject)
{
	var cn = $('folderViewFrame').className; 
	$('folderViewFrame').className = cn.substr(0, cn.length-15);
	if(dataObject.folders)
	{
		for(var i = 0; i < dataObject.folders.length; i++)
		{	
			$('folderView').appendChild(folderNode(dataObject.folderIds[i], dataObject.folders[i]));
		}
	}
}

function noOp(){}

function folderNode(id, name)
{
	var c = document.createElement('div');
	c.className = 'folderItem';
	var a = document.createElement('a');
	a.id = 'folderItem_'+id;
	a.href= 'javascript:noOp();';
	org.bambuscms.gui.setEventHandler(a, 'click', openAssignedFileList);
	var s = document.createElement('span');
	var t = document.createTextNode(name);
	s.appendChild(t);
	a.appendChild(s);
	c.appendChild(a);
	return c;
}

function openAssignedFileList(event)
{
	var id = this.id.substr(11);
	updateFileView(id);
	var elms = $('folderView').getElementsByTagName('a');
	for(var i = 0; i < elms.length; i++)
	{
		elms[i].className = (id == i) ? 'active' : '';
	}
}

function updateFileView(folder)
{
	$('fileView').innerHTML = '';
	if(folder != -1)
	{
		var data = {
			'controller':'org.bambuscms.applications.files',
			'call':'getFiles'
		};
		$('fileViewFrame').className += ' waitingForData';
		var qobj = org.bambuscms.http.managementRequestURL(data);
		org.bambuscms.http.fetchJSONObject(
			qobj,
			drawFileView,
			'{"folder":"'+folder+'"}'
		);
		//drawFileView({'ids':[0], 'items':['test'], 'types':[0], 'typeNames':['test'], 'typeIcons':['test']});
	}
}

function drawFileView(obj)
{
	var cn = $('fileViewFrame').className; 
	$('fileViewFrame').className = cn.substr(0, cn.length-15);
	if(obj.ids)
	{
		var id, name, type, icon;
		for(var i = 0; i < obj.ids.length; i++)
		{
			id   = obj.ids[i];
			name = obj.items[i];
			type = obj.typeNames[obj.types[i]];
			icon = obj.typeIcons[obj.types[i]];
			$('fileView').appendChild(fileNode(id, name, type, icon));
		}
	}
	
}
function fileNode(id, name, type, icon)
{
	var c = document.createElement('div');
	c.className = 'fileItem';
	var a = document.createElement('a');
	a.id = 'fileItem_'+id;
	//org.bambuscms.gui.setEventHandler(a, 'click', openAssignedFileInfo);
	var s = document.createElement('span');
	var t = document.createTextNode(id+': '+name+' ['+type+'#'+icon+']');
	s.appendChild(t);
	a.appendChild(s);
	c.appendChild(a);
	return c;
}
org.bambuscms.autorun.register(updateFolderView);


///////////////////////////////////////



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
		image.style.background = cSelectedObject;
		select.checked = true;
	}
	else
	{
		image.style.background = '#fff';
		select.checked = false;
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
function filter(query)
{
	query = query.toLowerCase();
	if(query == '')
	{
		selectItems(false);
	}
	else
	{
		inputs = document.getElementsByTagName('input');
		var id,image,select;
		for(var i = 0; i < inputs.length; i++)
		{
			if(inputs[i].name.substr(0,7) == 'select_')
			{
				id = inputs[i].name.replace(/select_/, "");
				image = document.getElementById(id);
				select = document.getElementById('select_'+id);
				if(document.getElementById('img_'+id).title.toLowerCase().indexOf(query) != -1)
				{
					image.style.background = cSelectedObject;
					select.checked = true;
				}
				else
				{
					image.style.background = '#fff';
					select.checked = false;					
				}
			}
		}		
	}
}
function selectItems(allOrNone)
{
	if(allOrNone)
	{
		var check = true;
		var background = cSelectedObject;
	}
	else
	{
		var check = false;
		var background = '#fff';
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
function toggleGroup()
{
	spans = document.getElementsByTagName('span');
	var span = '';
	for(var i = 0; i < spans.length; i++)
	{
		if(spans[i].className == 'hiddenGroup')
		{
			spans[i].className = 'group';
		}
		else if(spans[i].className == 'group')
		{
			spans[i].className = 'hiddenGroup';
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
