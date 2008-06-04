function Upload()
{
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
	if(document.getElementById('downloadIFrames'))
	{
		document.getElementById('downloadIFrames').innerHTML = '';
	}
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
		imageEnlarge.innerHTML = '<img src="'+systemMediumActionImage+'action-flat-close.png" onclick="hideEnlarge()" alt="" class="dialogClose" /><h2 id="imageEnlargeTitle"></h2><div id="imageEnlargeContainer"></div><div id="imageEnlargeNavigator"></div>';
		var imageEnlargeTitle =  document.getElementById('imageEnlargeTitle');
		var imageEnlargeContainer =  document.getElementById('imageEnlargeContainer');
		
		if(window.innerHeight >= 400){
			imageEnlarge.style.height = (window.innerHeight - 100)+'px';
			imageEnlargeContainer.style.height = (window.innerHeight - 220)+'px';
		}
		imageEnlarge.style.width =  (window.innerWidth - 200)+'px';
		
		imageEnlargeTitle.innerHTML = images[0];
		imageEnlargeContainer.innerHTML = '<img id="imageEnlargeImg" src="'+image_uri+images[0]+'" alt="" />';
		
		if(images.length > 1)
		{
			var imageEnlargeNavigator =  document.getElementById('imageEnlargeNavigator');
			imageEnlargeNavigator.innerHTML = '<img onclick="goImg(-1)" src="'+systemMediumActionImage+'action-flat-backward.png" alt="&lt;" style="float:left;" />';
			imageEnlargeNavigator.innerHTML += '<img onclick="goImg(1)" src="'+systemMediumActionImage+'action-flat-forward.png" alt="&gt;" style="float:right;" />';
			
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
function cssDefinitions(){
    var editor = document.getElementById('editorianid');
    var select = document.getElementById('cssdefselector');
    if(select){
        for(var i = 0;i < select.length;i++){//>
            if(select.options[i].value != '')
                select.options[i] = null;
        }
        var str = editor.value;
        var definitions = str.match(/([0-9a-zA-Z._<>:\t #]+)[\s]?\{/g);
        var typechar;
        var tmp;
        for(var i = 0;i < definitions.length;i++){//>
            tmp = definitions[i].split('\n');
            tmp = tmp[0].split('{');
            definitions[i] = tmp[0];
            typechar = definitions[i].substr(0,1);
            if(typechar != '.' && typechar != '#'){
                definitions[i] = ' ' + definitions[i];
            }
        }
        definitions.sort();
        for(var i = 0;i < definitions.length;i++){//>
            typechar = definitions[i].substr(0,1);
            if(typechar == ' '){
                definitions[i] = definitions[i].substr(1);
            }
        }
        for(var k = 0;k < definitions.length;k++){//>
            select.options[k+1] = new Option(definitions[k], definitions[k]);
        }
    }
    
}
function inarray(needle, haystack)
{
    for(var i = 0; i < haystack.length; i++)
    {
        if(haystack[i] == needle) return true;
    }
    return false;
}
function cssColors()
{
    var editor = document.getElementById('editorianid');
    var select = document.getElementById('csscolorselector');
    if(select)
    {
        for(var i = 0;i < select.length;i++)
        {
            if(select.options[i].value != '')
                select.options[i] = null;
        }
        var str = editor.value;
        var found = str.match(/(#[0-9a-zA-Z][0-9a-zA-Z][0-9a-zA-Z]+)[\s]?[;]/g);
        if(found && found.length > 0)
        {
        	definitions = new Array();
        	for(var i = 0; i < found.length; i++)
        	{
    			found[i] = found[i].substr(0,found[i].length-1);
        		if(!inarray(found[i], definitions))
        			definitions[definitions.length] = found[i];
        	}
	        definitions.sort();
	        for(var k = 0;k < definitions.length;k++)
	        {
	            select.options[k+1] = new Option(definitions[k], definitions[k], definitions[k]);
	            select.options[k+1].style.background = definitions[k];
	            color = (definitions[k].length < 6) ? definitions[k].substr(1,3) : definitions[k].substr(1,6);
	            select.options[k+1].style.color = textColorFor(color);
	        }
        }
        select.selectedIndex = 0;
    }
    
}

function textColorFor(color)
{
	var r1,r2,g1,g2,b1,b2,r,g,b,h;
	var collen = color.length;
	color = color.toLowerCase();
	if(collen == 3)
	{
		var tmp = '';
		tmp += color.substr(0,1)+color.substr(0,1)
		tmp += color.substr(1,1)+color.substr(1,1)
		tmp += color.substr(2,1)+color.substr(2,1)
		color = tmp;
	}
	else if(collen != 6)
	{
		color = '000000';
		collen = 6;
	}
	r1 = color.charCodeAt(0) - ((color.charCodeAt(0) < 60) ?  48 : 87);
	r2 = color.charCodeAt(1) - ((color.charCodeAt(1) < 60) ?  48 : 87);
	g1 = color.charCodeAt(2) - ((color.charCodeAt(2) < 60) ?  48 : 87);
	g2 = color.charCodeAt(3) - ((color.charCodeAt(3) < 60) ?  48 : 87);
	b1 = color.charCodeAt(4) - ((color.charCodeAt(4) < 60) ?  48 : 87);
	b2 = color.charCodeAt(5) - ((color.charCodeAt(5) < 60) ?  48 : 87);
	
	r = r1 * 16 + r2;
	g = g1 * 16 + g2;
	b = b1 * 16 + b2;
	h = (r1 * 16 + r2) + (g1 * 16 + g2) +(b1 * 16 + b2);
	if(h < 300)
	{
		return '#ffffff';
	}
	else
	{
		return '#000000';
	}
}

function gotoCSSDef(definition){
    if(definition != undefined){
        var editor = document.getElementById('editorianid');
        var height = editor.scrollHeight;
        var str = editor.value;
        var matches_array = str.split("\n");//str.match(regexp);
        var lines = matches_array.length;
        var pxperline = Math.round(height/lines);
        var defregexp = new RegExp('^[\s]?'+definition);
        var line = -1;
        for(var i = 0;i < lines;i++){//>
            if(matches_array[i].search(defregexp) != -1){
                line = i;
            }
        }   
        if(line != -1){
            editor.scrollTop = line*pxperline;
        }
    }
}
function gotoCSSColor(definition){
    if(definition != undefined)
    {
    	alert(definition);
        var editor = document.getElementById('editorianid');
        var height = editor.scrollHeight;
        var str = editor.value;
        var matches_array = str.split("\n");
        var lines = matches_array.length;
        var pxperline = Math.round(height/lines);
        var defregexp = new RegExp(definition);
        var line = -1;
        var i = 0;
        var offset = 0;
        while(line == -1 && i < lines)
        {
            if(matches_array[i].search(defregexp) != -1)
            {
            	offset += matches_array[i].search(defregexp);
                line = i;
            }
            else
            {
            	offset += matches_array[i].length+1;
            }
        	i++;
        }   
        if(line != -1)
        {
    	  //  if(typeof document.selection != 'undefined') 
    	  //  {
		  //    /*MSIE*/
		  //      var range = document.selection.createRange(offset, 6);
		  //  }
		  //  else 
		  	var len = (definition.length < 6) ? 4 : 7;
		    if(typeof editor.selectionStart != 'undefined'){
			    /*Mozilla*/
		        editor.selectionStart = offset;
		        editor.selectionEnd = offset+len;
		    }
            editor.scrollTop = line*pxperline;
            editor.focus();
        }
    }
}
function show_selector(sel){
    if(sel == ''){
        document.getElementById('selector').style.visibility = 'hidden';
        document.getElementById('selector').style.display = 'none';
        document.getElementById('selector').style.width = '0px';
    }else{
        document.getElementById('selector').style.visibility = 'visible';
        document.getElementById('selector').style.display = 'block';
        document.getElementById('selector').style.width = '110px';
        var ch = 0;
        for (var i = 0; i < document.getElementById('selector').getElementsByTagName("div").length; i++) {//>
            if(document.getElementById('selector').getElementsByTagName("div")[i].className == sel){
                document.getElementById('selector').getElementsByTagName("div")[i].style.visibility = 'visible';
                document.getElementById('selector').getElementsByTagName("div")[i].style.display = 'block';
            }else{
                document.getElementById('selector').getElementsByTagName("div")[i].style.visibility = 'hidden';
                document.getElementById('selector').getElementsByTagName("div")[i].style.display = 'none';
            }
        }
    }
}
