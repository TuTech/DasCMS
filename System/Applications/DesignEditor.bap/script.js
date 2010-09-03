function Create()
{
	input = $c('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	org.bambuscms.app.dialog.create(_('create_new_stylesheet'), _('filename'), input, _('create'), _('cancel'));
	org.bambuscms.app.dialog.setAction('create');
	input.focus();
}

function Delete()
{
	input = $c('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	org.bambuscms.app.dialog.create(_('delete_stylesheet'), _('do_you_really_want_to_delete_this_script'), input, _('yes_delete'), _('no'));
	org.bambuscms.app.dialog.setAction('delete');
}
org.bambuscms.editor.wysiwyg.commitAll = function(){
	var ta = $('org_bambuscms_app_document_editorElementId');
	if(ta.bespin){
		ta.value = ta.bespin.editor.value;
	}
}
org.bambuscms.display.addCallback(function(){
	if(bespin){
		var ta = $('org_bambuscms_app_document_editorElementId');
		ta.bespin.dimensionsChanged();
	}
});

org.bambuscms.app.document.insertMedia = function(type, url, title)
{
	var insert = '';
	switch(type)
	{
		case 'image':
			org.bambuscms.app.document.insertText(' url("'+url+'")');
			break;
	}
};

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
    org.bambuscms.wnotifications.report(org.bambuscms.wnotifications.INFORMATION, _('css_reformatted'));
}
var isSorting = false;
function sortCSS(msg){
	if(isSorting)
	{
		return
	}
	isSorting = true;
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
    org.bambuscms.wnotifications.report(org.bambuscms.wnotifications.INFORMATION, _('css_sorted'));
}
