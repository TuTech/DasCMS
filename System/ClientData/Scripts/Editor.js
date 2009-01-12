////////////////////////////////////////////////////////////////////////////////
// HTML editor functions 
////////////////////////////////////////////////////////////////////////////////

function addTag(msg, tag){
    var text = selectedText(msg);
    if (text != ''){
        insertText('<'+tag+'>'+text+'</'+tag+'>');
    }
    EditorFocus();
}
function addBreak(){
    insertText('<br />');
    EditorFocus();
}
function addLink(){
    var url  = BCMSPrompt('please_insert_link_url', 'http://');
    if((url != undefined) && (url != '')){
        var desc = BCMSPrompt('please_insert_link_title', selectedText(false));
        desc = ((desc == undefined) || (desc == '')) ? url : desc; 
        if(url.substr(0,1) == '#')
        {
        	insertText('<a href=\"'+url+'\">'+desc+'</a>');
        }
        else
        {
        	insertText('<a href=\"'+url+'\" target=\"_blank\">'+desc+'</a>');
        }
    }
    EditorFocus();
}
function createList(listType){
    if(true){
        var seltext = selectedText();
        var items = seltext.split('\012');
        var listitems = '';
        listType = (listType) ? 'ol' : 'ul';
        for(var i = 0; i < items.length; i++){
            if(items[i] != ''){
                listitems += '    <li>' + items[i] + '</li>\012';
            }
        }
        seltext = '<'+ listType +'>\012' + listitems + '</'+ listType +'>';
        insertText(seltext);
    }
    EditorFocus();
}


////////////////////////////////////////////////////////////////////////////////
// HTML editor functions 
////////////////////////////////////////////////////////////////////////////////

function cleanHTML(){
    var htmlElem =  document.getElementById('editorianid');
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
    EditorFocus();
}


////////////////////////////////////////////////////////////////////////////////
// general editor functions 
////////////////////////////////////////////////////////////////////////////////

function setScrollPos(pos){
    if(!isNaN(pos)) 
        document.getElementById('editorianid').scrollTop = pos;
    EditorFocus();
}

function insertText(text){
    var textarea = document.getElementById('editorianid');
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
        var elem = document.getElementById('warning');
        elem.innerHTML = 'your_browser_does_not_support_this_feature_correctly';       
        textarea.value = textarea.value + text; 
    }
    EditorFocus();
}
function selectedText(msg){
    var textarea = document.getElementById('editorianid');
    textarea.focus();
    if(typeof document.selection != 'undefined') {
    /*MSIE*/
        var range = document.selection.createRange();
        return range.text;
    }else if(typeof textarea.selectionStart != 'undefined'){
    /*Mozilla*/
        return (textarea.value.substring(textarea.selectionStart, textarea.selectionEnd));
    }else{
        var elem = document.getElementById('warning');
        elem.innerHTML = 'your_browser_does_not_support_this_feature_correctly';       
        if(msg != false){
            var text = BCMSPrompt(msg, '');
            return text;
        } else{
            return '';
        }
    }
}
function curpos(){
    var elem;
    if(elem = document.getElementById('linenr'))
    {
	    var textarea = document.getElementById('editorianid');
	    textarea.focus();
	    if(typeof textarea.selectionStart != 'undefined'){
	        var position_start = textarea.selectionStart;           //Cursoranfangsposition
	        var position_end = textarea.selectionEnd;               //Cursorendposition
	        var passed = textarea.value.substring(0,position_start);//text vom Anfang des Textfeldes bis zur Cursorposition
	        var open_tags = new Array();                            
	        var open_tags = passed.match(/\n/g);                    //Zaehle die zeilenumbrueche in passed
	        var diff = 0;
	        var displ =  position_start;                            //Zeichennr
	        if(!open_tags){                                         //Kein Zeilenumbruch -> Zeile 1
	            tst = 1;
	        }else{                                                  //Zeilenumbrueche beginnend bei 1
	            var tst = open_tags.length + 1;
	        }
	        if(tst > 1){                                            //Zeilennr. > 1
	            passed = passed.substring(passed.lastIndexOf('\n'));//Zaehle Zeichen in der Aktuellen Zeile bis zur Cursorposition
	            displ = passed.length-1;                            //Zeichennr
	        }
	        if(position_start != position_end){                     //Cursoranfangsposition = Cursorendposition
	            diff = (position_end-position_start);               //Gewaehlte Zeichen berechnen
	            var displ = displ+' ['+diff+']';                    //Zeichennr + Auswahl
	        }
	        elem.innerHTML = tst+':'+displ;              //Anzeigen
	    }
    }
}
function old_sad_function(){
	commitChanges();
    var srch  = BCMSPrompt('Suchen nach:', '');
    var temp = new Array();
    for(var i = 0; i < srch.length; i++){
        temp[i] = srch.substring(i, (i+1));
    }
    var srchlen = i;
    if(srchlen > 0){
        var rplc  = BCMSPrompt('Ersetzen durch:', '');
        if(srch != rplc){
            var textarea = document.getElementById('editorianid');
            textarea.focus();
            var txt = textarea.value;
            for(var i = 0; i < txt.length; i++){
                if(txt.substring(i, (i+srchlen)) == srch){
                    txt = txt.substring(0,i)+rplc+txt.substring((i+srchlen));
                    i = i + rplc.length - 1;
                }
            }
            textarea.value = txt;
        }
    }
    setContent(document.getElementById('editorianid').value);
}
function addImport(elemid){
    var elem = document.getElementById(elemid);
    var text = elem.innerHTML;
    text = text.replace(/<!--/g, "");
    text = text.replace(/-->/g, "");
    text = text.replace(/&amp;/g, "&");
    text = text.replace(/&lt;/g, "<");
    text = text.replace(/&gt;/g, ">");
    insertText(text);
}
