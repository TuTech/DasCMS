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
	if(!bWYSIWYGEnabled)
	{
		insertText(insert);
	}
	else
	{
		doRichEditCommand('insertHTML', insert);
	}
}


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
			document.getElementById('downloadIFrames').innerHTML += '<iframe src="download.php?path='+path+'&file='+id+'" class="downloadIFrame" />';
		}
	}	
}
