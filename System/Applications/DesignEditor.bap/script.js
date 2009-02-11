org.bambuscms.app.document.insertMedia = function(type, id, title)
{
	var insert = '';
	switch(type)
	{
		case 'image':
			org.bambuscms.app.document.insertText(' url("'+url+'")');
			break;
	}
};
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
    var cssElem =  $(org.bambuscms.app.document.editorElementId);
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
}
var isSorting = false;
function sortCSS(msg){
	if(isSorting)
	{
		return
	}
	isSorting = true;
	document.getElementById('js_message').style.opacity = 1.0;
    var editor = $(org.bambuscms.app.document.editorElementId);
    var topScroll = editor.scrollTop;
    var leftScroll = editor.scrollLeft;
    
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
    			y++;
    		}
    	}
    	else
    	{
	    	//editor.value += i+'>> '+parts[i][0]+'\n';
    		sorted[sorted.length] = parts[i][0];
    		y++;
    	}
    }
    for(var i = 0;i < sorted.length;i++){
    
    	sorted[i] = sorted[i].replace(/{/g, "\n{\n    ");
     	sorted[i] = sorted[i].replace(/;/g, ";\n    ");
     	sorted[i] = sorted[i].replace(/    }/g, "}\n");
    
        finalCSS += sorted[i]+'\n';
    }
    editor.value = finalCSS;
    editor.scrollTop = topScroll;
    editor.scrollLeft = leftScroll;    
    isSorting = false;
}
