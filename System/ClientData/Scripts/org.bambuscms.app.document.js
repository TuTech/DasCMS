org.bambuscms.app.document = {};
org.bambuscms.app.document.editorElementId = 'org_bambuscms_app_document_editorElementId';
org.bambuscms.app.document.formElementId = 'documentform';

org.bambuscms.app.document.open = function(){org.bambuscms.wopenfiledialog.toggle()};
org.bambuscms.app.document.createNew = function(){};
org.bambuscms.app.document.deleteCurrent = function(){};
org.bambuscms.app.document.orderContent = function(){};
org.bambuscms.app.document.cleanContent = function(){};
org.bambuscms.app.document.save = function()
{
	if(org.bambuscms.editor.wysiwyg.commitAll)
	{
		org.bambuscms.editor.wysiwyg.commitAll();
	}
	if(!document.documentform.submit())
	{
		$(org.bambuscms.app.document.formElementId).submit();
	}
};
org.bambuscms.app.document.saveAs = function(inputId)
{
	if($(inputId))
	{
		var p = '';
		p = prompt('save_and_rename_to', document.getElementById(inputId).value);
		if(p != '' && p != null && document.getElementById(inputId).value != p)
		{
			document.getElementById(inputId).value = p;
			org.bambuscms.app.document.save();
		}
	}
};

org.bambuscms.app.document.insertText = function(text){
    var textarea = $(org.bambuscms.app.document.editorElementId);
    textarea.focus();
    if(typeof document.selection != 'undefined') {
    /*MSIE*/
        var range = document.selection.createRange();
        range.text = text;
        range = document.selection.createRange();
        range.moveStart('character', text.length);      
        range.select();
    }else if(typeof textarea.selectionStart != 'undefined'){
    /*Mozilla*/
        var position_start = textarea.selectionStart;
        var position_end = textarea.selectionEnd;
        var topScroll = textarea.scrollTop;
        var leftScroll = textarea.scrollLeft;
        textarea.value =
            textarea.value.substring(0,position_start)+
            text+
            textarea.value.substring(position_end, textarea.textLength);
        textarea.selectionStart = (position_start + text.length);
        textarea.selectionEnd = (position_start + text.length);
        textarea.scrollLeft = leftScroll;
        textarea.scrollTop = topScroll;
        return true;
    }else{
        textarea.value = textarea.value + text; 
        org.bambuscms.wnotifications.report(org.bambuscms.wnotifications.WARNING, 'your browser does not support this feature');
    }
    textarea.focus();
};

org.bambuscms.app.document.cleanHTML = function(){
    var htmlElem =  $(org.bambuscms.app.document.editorElementId);
    var topScroll = htmlElem.scrollTop;
    var leftScroll = htmlElem.scrollLeft;
    var html = htmlElem.value;
    
    
    html.replace(/[\s]*/g, " ");
    //whitespace
    html = html.replace(/ </g, "<");
    html = html.replace(/> /g, ">");
    html = html.replace(/ >/g, ">");
    html = html.replace(/< /g, "<");
    html = html.replace(/</g, "\n<");
    html = html.replace(/>/g, ">\n");
    html = html.replace(/\/>/g, "\/>\n");

	//xhtml - add tailing "/" to some elements
	while(html.match(/(<(hr|br|wbr|input|img)[\s]*[^\/]*)>/g))
	{
		html = html.replace(/(<(hr|br|wbr|input|img)[\s]*[^\/]*)>/g, "$1 />");
	}
    //MSOffice crap
    html = html.replace(/<o:/g, "<");//remove office namespace prefix
    html = html.replace(/<\/o:/g, "</");
    html = html.replace(/[\s]+class="MsoNormal"/g, "");//remove office class
    html = html.replace(/[\s]+style="text-align: justify;"/g, "");//text align should be set in css file
    while(html.match(/(<[a-zA-Z0-9-_:]+)(|[\s]+[^>]+)[\s]+[a-zA-Z0-9-_]+=['"]{1}[\s]*['"]{1}/g))
    {
    	html = html.replace(/(<[a-zA-Z0-9-_:]+)(|[\s]+[^>]+)[\s]+[a-zA-Z0-9-_]+=['"]{1}[\s]*['"]{1}/g, "$1 $2");
    }
	html = html.replace(/[\s]+>/g, ">");//tailing space in tag
    html = html.replace(/[\n]+[\s]* /g, "\n");//remove empty lines
	

	//remove <p><br></p>
	while(html.match(/<(p|span)>[\n\s]*<br\s*(|\/[\s]*)>[\n\s]*<\/\1>/g))
	{
		html = html.replace(/<(p|span)>[\n\s]*<br\s*(|\/[\s]*)>[\n\s]*<\/\1>/g, "<br />");
	}
	html = html.replace(/<([a-zA-Z0-9-_:]+)>[\n\s]*<\/\1>/g, "");

    
    var lines = html.split("\n");
   	var intendcount = 0;
   	var intendspace = "    ";
   	var erg = "";
   	var nlflag = false;
   	var firstInTag = false;
   	for(var i = 0;i < lines.length;i++)
   	{
   		intend = "";
   		if(lines[i].substr(0,1) == '<' && lines[i].substr(1,1) != '!' && lines[i].substr(1,1) == '/')
   		{
			intendcount--;
	   		for(var h = 0;h < intendcount;h++)
   			{
   				intend += intendspace;
   			}
   		}
   		else if(lines[i].substr(0,1) == '<' && lines[i].substr(1,1) != '!' && lines[i].substr(1,1) != '/')
   		{
	   		for(var h = 0;h < intendcount;h++)
   			{
   				intend += intendspace;
   			}
   			if(lines[i].substr(-2,1) != '/')
   			{
				intendcount++;
			}
   		
   		}
   		else
   		{
	   		for(var h = 0;h < intendcount;h++)
   			{
   				intend += intendspace;
   			}   			
   		}
   		
   		if(!(i == 0 && lines[i] == ""))
   		{
   			if(lines[i].replace(/[\s]*/, '') == '')
   			{
   				nlflag = true;
   				continue;
   			}
   			if(!firstInTag && nlflag && lines[i].substring(0,1) != '<')
   			{
   				erg += "\n";
   			}
   			erg += intend+lines[i]+"\n";
   			firstInTag =(lines[i].substr(lines[i].length-1,1) == '>')
   			nlflag = false;
   		}
   	}
   	html = erg.substr(0, erg.length - 1);
    htmlElem.value = html;
    htmlElem.scrollTop = topScroll;
    htmlElem.scrollLeft = leftScroll;
    org.bambuscms.wnotifications.report(org.bambuscms.wnotifications.INFORMATION, 'html reformatted');
};
org.bambuscms.app.document.searchAndReplaceFormHelper = function()
{
	var r = $('org_bambuscms_app_document_replace').value;
	var s = $('org_bambuscms_app_document_search').value;
	org.bambuscms.app.dialog.cancel();
	org.bambuscms.app.document.searchAndReplace(s,r);
}
org.bambuscms.app.document.searchAndReplace = function(lookFor, replaceWith)
{
	if(lookFor == undefined || lookFor == '')
	{
		var div = document.createElement('div');
		var search = document.createElement('input'); 
		var replace = document.createElement('input');
		search.type = 'text';
		search.id = 'org_bambuscms_app_document_search';
		replace.type = 'text';
		replace.id = 'org_bambuscms_app_document_replace'
		var slabel = document.createElement('label');
		slabel.setAttribute('for', 'org_bambuscms_app_document_search');
		var rlabel = document.createElement('label');
		rlabel.setAttribute('for', 'org_bambuscms_app_document_replace');
		slabel.innerHTML = 'look for:';
		rlabel.innerHTML = 'replace with:';
		div.appendChild(slabel);
		div.appendChild(search);
		div.appendChild(rlabel);
		div.appendChild(replace);
		org.bambuscms.app.dialog.create(
			'search and replace',
			'',
			div,
			'replace all',
			'abort',
			false,
			'org.bambuscms.app.document.searchAndReplaceFormHelper'
		);
	}
	if(lookFor && lookFor.length > 0)
	{
		if(replaceWith == undefined)
		{
			replaceWith = '';
		}
		var textarea = document.getElementById(org.bambuscms.app.document.editorElementId);
        var txt = textarea.value;
		var srchlen = lookFor.length;
        for(var i = 0; i < txt.length; i++)
        {
            if(txt.substring(i, (i+srchlen)) == lookFor)
            {
                txt = txt.substring(0,i)+replaceWith+txt.substring((i+srchlen));
                i = i + replaceWith.length - 1;
            }
        }
        textarea.value = txt;
        org.bambuscms.wnotifications.report(org.bambuscms.wnotifications.INFORMATION, 'all elements replaced');
		textarea.focus();
	}
	
};

org.bambuscms.app.document.insertMedia = function(type, url, title)
{
	var insert = '';
	switch(type)
	{
		case 'file':
			insert=(' <a href="'+url+'" target="_blank">'+title+'</a> ');
			break;
		case 'image':
			insert=('<img src="'+url+'" alt="'+title+'" title="'+title+'" />');
			break;
	}
	org.bambuscms.app.document.insertText(insert);
};

