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
function getSelectedImages()
{
	images = new Array();
	allimages = new Array();
	inputs = document.getElementsByTagName('input');
	var parent = '';
	var dismissed = '';
	for(var i = 0; i < inputs.length; i++)
	{
		if(inputs[i].name.substr(0,7) == 'select_')
		{
			parent = inputs[i].name.replace(/select_/, "");
			if(inputs[i].checked)
			{
				images[images.length] = document.getElementById(parent).title;
			}
			allimages[allimages.length] = document.getElementById(parent).title;
		}
	}
	if(images.length == 0)
		return allimages;
	else
		return images;
}
var enlargedImage = 0;
var enlargedImages;

function enlargeSelected()
{
	images = new Array();
	images = getSelectedImages();
	enlargedImages = images;
	if(images.length > 0)
	{
		if(!document.getElementById('imageEnlarge'))
		{
			document.getElementById("bambusJAX").innerHTML += '<div id="imageEnlarge" />';
		}
		var imageEnlarge =  document.getElementById('imageEnlarge');
		imageEnlarge.style.display = 'block';
		imageEnlarge.innerHTML = '<img src="'+systemHugeIconPath+'flatclose.png" onclick="hideEnlarge()" alt="" class="dialogClose" /><h2 id="imageEnlargeTitle"></h2><div id="imageEnlargeContainer"></div><div id="imageEnlargeNavigator"></div>';
		var imageEnlargeTitle =  document.getElementById('imageEnlargeTitle');
		var imageEnlargeContainer =  document.getElementById('imageEnlargeContainer');
		
		if(window.innerHeight >= 500){
			imageEnlarge.style.height = (window.innerHeight - 200)+'px';
			imageEnlargeContainer.style.height = (window.innerHeight - 320)+'px';
		}
		imageEnlarge.style.width =  (window.innerWidth - 200)+'px';

		
		imageEnlargeTitle.innerHTML = images[0];
		imageEnlargeContainer.innerHTML = '<img id="imageEnlargeImg" src="'+image_uri+images[0]+'" alt="" />';
		
		if(images.length > 1)
		{
			var imageEnlargeNavigator =  document.getElementById('imageEnlargeNavigator');
			imageEnlargeNavigator.innerHTML = '<img onclick="goImg(-1)" src="'+systemHugeIconPath+'flatbackward.png" alt="&lt;" style="float:left;" />';
			imageEnlargeNavigator.innerHTML += '<img onclick="goImg(1)" src="'+systemHugeIconPath+'flatforward.png" alt="&gt;" style="float:right;" />';
			
		}
	}
}
function hideEnlarge()
{
	var elem = document.getElementById("imageEnlarge");
	elem.style.display = 'none';
	elem.innerHTML = '';
}
function goImg(offset)
{
	var src;
	if(offset > 0 && enlargedImages.length > enlargedImage+1)
	{
		src = enlargedImages[++enlargedImage];
	}
	else if(offset < 0 && enlargedImage > 0)
	{
		src = enlargedImages[--enlargedImage];
	}
	else if(offset < 0 && enlargedImage == 0)
	{
		enlargedImage = enlargedImages.length-1;
		src = enlargedImages[enlargedImage];
	}
	else
	{
		src = enlargedImages[0];
		enlargedImage = 0;
	}
	document.getElementById('imageEnlargeTitle').innerHTML = src;
	document.getElementById("imageEnlargeImg").src = image_uri+src;
}