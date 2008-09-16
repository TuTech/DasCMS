function Upload()
{
    var div, finput, cinput, cdesc, br, minput;
	div = document.createElement('div');
	
	finput = document.createElement('input');
	finput.setAttribute('name','bambus_image_file');
	finput.setAttribute('type','file');
		
	minput = document.createElement('input');
	minput.setAttribute('name','MAX_FILE_SIZE');
	minput.setAttribute('type','hidden');
	minput.setAttribute('value','1000000000');
	
	cinput = document.createElement('input');
	cinput.setAttribute('name','bambus_overwrite_image_file');
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




/******************/
var doclientredraw = true;
function insertMedia(type, url, title)
{
	var insert = '';
	switch(type)
	{
		case 'image':
			insertText(' url("'+url+'")');
			break;
	}
}


function clientredraw()
{
	if(document.getElementById('imageEnlargeContainer'))
	{
		var imageEnlargeContainer =  document.getElementById('imageEnlargeContainer');
		var imageEnlarge =  document.getElementById('imageEnlarge');
		if(window.innerHeight >= 400){
			imageEnlarge.style.height = (window.innerHeight - 100)+'px';
			imageEnlargeContainer.style.height = (window.innerHeight - 220)+'px';
		}
		imageEnlarge.style.width =  (window.innerWidth - 200)+'px';
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
			document.getElementById('downloadIFrames').innerHTML += '<iframe src="Management/download.php?path=design&file='+id+'" class="downloadIFrame" />';
		}
	}	
	//window.setTimeout("{document.getElementById('downloadIFrames').innerHTML = '';}",1000);
}






////////////////////////////////////////////////////////////////////////////////
// CSS editor functions 
////////////////////////////////////////////////////////////////////////////////
function changePaletteTo(pID)
{
	var palettes = document.getElementById('objectInspector').getElementsByTagName('table');
	for(var i = 0; i < palettes.length;i++)
    {
		palettes[i].style.display = 'none';
    }
	document.getElementById(pID).style.display = 'table';
}

function cleanCSS(){
	var msg = cleanupmesseage;
    var cssElem =  document.getElementById('editorianid');
    var topScroll = cssElem.scrollTop;
    var leftScroll = cssElem.scrollLeft;
    var css = cssElem.value;
    css = css.replace(/[\n]/g, " ");
    css = css.replace(/[\t]/g, " ");
    css = css.replace(/[\r]/g, " ");
    while(css.match(/  /g)){
        css = css.replace(/  /g, " ");
    }
    css = css.replace(/[\s]?\{[\s]?/g, "{\n    ");
    css = css.replace(/[\s]?\}[\s]?/g, "}\n");
    css = css.replace(/[\s]?;[\s]?/g, ";\n    ");
    css = css.replace(/[ ]+\}/g, "}");
    css = css.replace(/\*\//g, "\*\/\n");
    css = css.replace(/\}\n[\s]+/g, "}\n");
    css = css.replace(/^[\s]+/g, "");
    cssElem.value = css;
    cssElem.scrollTop = topScroll;
    cssElem.scrollLeft = leftScroll;
    fademessage(msg);
}
var isSorting = false;
function sortCSS(msg){
	if(isSorting)
	{
		return
	}
	isSorting = true;
	document.getElementById('js_message').style.opacity = 1.0;
    var editor = document.getElementById('editorianid');
    var topScroll = editor.scrollTop;
    var leftScroll = editor.scrollLeft;
    fademessage('normalizing_css '+(Math.round(prcnt * i))+'%');
    
    var str = editor.value;
    str = str.replace(/[\n]/g, " ");
    str = str.replace(/[\t]/g, " ");
    str = str.replace(/[\s]+/g, " ");
    str = str.replace(/[\r]/g, " ");
    str = str.replace(/}/g, "}\n");
    str = str.replace(/{ /g, "{");
    str = str.replace(/ }/g, "}");
    str = str.replace(/; /g, ";");
    str = str.replace(/ ;/g, ";");
    str = str.replace(/\*\//g, "\*\/\n");
    var definitions = str.split("\n");
    //editor.value = '';
    
    var defs = definitions.length;
    var prcnt = 100 / defs;
    
    var parts = new Array();
    parts[0] = new Array()
    var parti = 0;
    var lastWasComment = false;
    for(var i = 0;i < defs;i++)
    {
        definitions[i] = definitions[i].replace(/^[\s]+/g, "");
        definitions[i] = definitions[i].replace(/[\s]+$/g, "");
        if(definitions[i] != '')
        {
	        if(definitions[i].substr(0,2) == '/*')
	        {
	        	//it is a comment
	        	if(parts[parti].length > 0)
	        	{
	        		//only go to next part if current is not empty
	        		parts[++parti] = new Array();
	        		//editor.value += '>> new part\n';
	        	}
	        	lastWasComment = true;
	        }
	        else
	        {
	        	if(lastWasComment)
	        	{
	        		parts[++parti] = new Array();
	        		//editor.value += '>> new part\n';
	        		lastWasComment = false;
	        	}
	        }
	        //editor.value += definitions[i]+'\n';
	        parts[parti][parts[parti].length] = definitions[i];
        }
        fademessage('preparing_for_sort '+(Math.round(prcnt * i))+'%');
    }
    //editor.value = '';
    var sorted = new Array();
    var y = 0;
    var finalCSS = '';
    for(var i = 0; i < parts.length;i++)
    {
    	if(parts[i].length > 1)
    	{
    		//sort te entries in this part
    		parts[i].sort();
    		for(var g = 0; g < parts[i].length;g++)
    		{
    			//editor.value += i+':'+g+'> '+parts[i][g]+'\n';
    			sorted[sorted.length] = parts[i][g];
    			fademessage('sorting '+(Math.round(prcnt * y))+'%');
    			y++;
    		}
    	}
    	else
    	{
	    	//editor.value += i+'>> '+parts[i][0]+'\n';
    		sorted[sorted.length] = parts[i][0];
    		fademessage('sorting '+(Math.round(prcnt * y))+'%');
    		y++;
    	}
    }
    for(var i = 0;i < sorted.length;i++){
    
    	sorted[i] = sorted[i].replace(/{/g, "\n{\n    ");
     	sorted[i] = sorted[i].replace(/;/g, ";\n    ");
     	sorted[i] = sorted[i].replace(/    }/g, "}\n");
    
        finalCSS += sorted[i]+'\n';
        fademessage('finishing '+(Math.round(prcnt * i))+'%');
    }
    editor.value = finalCSS;
    editor.scrollTop = topScroll;
    editor.scrollLeft = leftScroll;    
    isSorting = false;
}
