var image_uri = 'System/Images/';
var translatedOpen = 'open';
var systemIcon = 'System/Icons/';
var systemMediumActionImage = systemIcon+'32x32/actions/';
var systemSmallActionImage = systemIcon+'22x22/actions/';
var cleanupmesseage = 'document_has_been_cleaned_up';

//new
function $(id)
{
	return document.getElementById(id);
}
function WCLFilter()
{
	if($('WContentLookupFilter') && $('WContentLookup'))
	{
	
		var filterStr = $('WContentLookupFilter').value;
		var elements = $('WContentLookup').getElementsByTagName('option');
		var str;
		//	for(var i = 0; i < elements.length; i++)
		//	{
		//		elements[i].style.display = 'none';
		//	}

			for(var i = 0; i < elements.length; i++)
			{
				str = elements[i].text;
				elements[i].style.display = (str.indexOf(filterStr) > -1) ? 'block' : 'none';
			}
	}
}









/////////////
//variables//
/////////////

//wysiwyg
var bWYSIWYGAllowed = false; //to be set true in browser specific js-scripts
var bWYSIWYGEnabled = false; //false = textarea-view / true = richttext-view
var bNoAppLoaded = false;
//BCMS Ids
var BCMSNotifier = 'notifier';
var BCMSContentArea = 'BambusContentArea';
var BCMSApplication = 'BambusApplication';
var BCMSWYSIWYGIFrameId = 'wysiwygeditor';
var BCMSWYSIWYGIFrameControlId = 'enabledWYSIWYGActions';
var BCMSTextEditorId = 'editorianid';
var BCMSTextEditorControlId = 'disabledWYSIWYGActions';
var BCMSWYSIWYGInitialized = false;
var BCMSBodyLoaded = false;
var sessionTimeOut;
var sessionTimeOutValue = 0;
//colors
cSelectedObject = '#aee27d';

//autostart function pointer - init with useless function
var BCMSRunFX = new Array();
var BCMSExitFX = new Array();
var BCMSWillSaveFX = new Array();

////////
//INIT//
////////

function BCMSDestroy()
{
	for(var i = 0; i < BCMSExitFX.length; i++)
		BCMSExitFX[i]();
}

function BCMSInitialize()
{
	BCMSBodyLoaded = true;
	for(var i = 0; i < BCMSRunFX.length; i++)
		BCMSRunFX[i]();
	RedrawAddId(BCMSWYSIWYGIFrameId, 0, 200);
	RedrawAddId(BCMSTextEditorId, 0, 130);
	Redraw();
	if(document.getElementById(BCMSNotifier))
	{
		//BCMSHideNotifier(runTotal, runCurrent, beginFadeout, intervall)
		BCMSHideNotifier(2000, 0, 1800, 50);
	}
	BCMSInitializeHotKeys();
	if(bWYSIWYGAllowed && document.getElementById(BCMSWYSIWYGIFrameId))
	{
		WYSIWYGStart();
	}
	try
	{
		//might not exist
		BCMSApplicationInitialize();
	}
	catch(e)
	{
		//does not matter
	}

}

function BCMSAlert(message)
{
	window.clearTimeout(ctrlKeyTimeOut);
	//EditorHideShortCutHelper();
	return alert(message);
}
function BCMSPrompt(title, preset)
{
	window.clearTimeout(ctrlKeyTimeOut);
	//EditorHideShortCutHelper();
	return prompt(title, preset);
}

var navShown = false;
var curNav = null;

function BCMSLogout(msg, confirmForExit, killSession)
{
	var exit = true;
	if(confirmForExit == '1')
	{
		exit = confirm(msg);
	}
	if(exit)
	{
		if(killSession == '1')
		{
			top.location = top.location+'?&logout=1';
		}
		else
		{
			top.location = '../';
		}
	}
}

////////////////////
//Editor functions//
////////////////////

function EditorSave()
{
	for(var i = 0; i < BCMSWillSaveFX.length; i++)
		BCMSWillSaveFX[i]();
	if(bWYSIWYGEnabled) WYSIWYGCommitText();
	if(!document.documentform.submit())
		document.getElementById('documentform').submit();
}
function EditorSaveAs(inputId)
{
	for(var i = 0; i < BCMSWillSaveFX.length; i++)
		BCMSWillSaveFX[i]();
	if(bWYSIWYGEnabled) WYSIWYGCommitText();
	if(document.getElementById(inputId))
	{
		var p = '';
		p = BCMSPrompt('save_and_rename_to', document.getElementById(inputId).value);
		if(p != '' && p != null && document.getElementById(inputId).value != p)
		{
			document.getElementById(inputId).value = p;
			if(!document.documentform.submit())
				document.getElementById('documentform').submit();
		}
	}
}
var ctrlKeyTimeOut;
function initeditor(){EditorStart();}
function EditorStart()
{
    curpos();
    actv();
}
function EditorSearchAndReplace(lookFor, replaceWith)
{
	if(lookFor == undefined || lookFor == '')
		lookFor = BCMSPrompt('look_for', '');
	if(lookFor && lookFor.length > 0)
	{
		var textarea = document.getElementById(BCMSTextEditorId);
        var txt = textarea.value;
		var srchlen = lookFor.length;
		if(replaceWith == undefined)
			replaceWith = BCMSPrompt('replace_with', '');
		if(bWYSIWYGEnabled) 
			WYSIWYGCommitText();
        for(var i = 0; i < txt.length; i++)
        {
            if(txt.substring(i, (i+srchlen)) == lookFor)
            {
                txt = txt.substring(0,i)+replaceWith+txt.substring((i+srchlen));
                i = i + replaceWith.length - 1;
            }
        }
        textarea.value = txt;
		textarea.focus();
		if(bWYSIWYGEnabled) 
			WYSIWYGApplyText();
		fademessage('all_elements_replaced');
	}
}


//////////////////////////
//WYSYIWYG: Mozilla/MSIE//
//////////////////////////

function WYSIWYGStart()
{
	WYSIWYGGetIFrameDocument(BCMSWYSIWYGIFrameId).designMode = "On";
	WYSIWYGCommand('useCSS',true);
	if(document.getElementById('WYSIWYGStatus'))
	{
		if(document.getElementById('WYSIWYGStatus').value == 'on')
		{
			WYSIWYGSwitch();
		}
	}
	else
	{
		WYSIWYGSwitch();
	}
}

function WYSIWYGSwitch()
{
	var TextEditor = document.getElementById(BCMSTextEditorId);
	var TextEditorControl = document.getElementById(BCMSTextEditorControlId);
	var RichTextEditor = document.getElementById(BCMSWYSIWYGIFrameId);
	var RichTextEditorControl = document.getElementById(BCMSWYSIWYGIFrameControlId);
	if(!document.getElementById('WYSIWYGStatus'))
	{
		document.getElementById('bambusJAX').innerHTML += 
			"<input type=\"hidden\" id=\"WYSIWYGStatus\" name=\"WYSIWYGStatus\" value=\"\" />";
	}
	var WYSIWYGStatus = document.getElementById('WYSIWYGStatus');
	if(bWYSIWYGEnabled)
	{
		//WYSIWYG is on -> deactivate
		bWYSIWYGEnabled = false;
		WYSIWYGStatus.value="off";
		WYSIWYGCommitText();
		
		RichTextEditor.className = 'hiddenEditor';
		RichTextEditorControl.style.display = 'none';
		
		TextEditor.className = "visibleEditor";
		TextEditorControl.style.display = 'block';
	}
	else
	{
		//WYSIWYG is off -> activate
		bWYSIWYGEnabled = true;
		WYSIWYGStatus.value="on";
		WYSIWYGApplyText();
		
		TextEditor.className = 'hiddenEditor';
		TextEditorControl.style.display = 'none';
		
		RichTextEditor.className = "visibleEditor";
		RichTextEditorControl.style.display = 'block';
	}
}

function EditorFocus()
{
	if(bWYSIWYGEnabled)
	{
		document.getElementById(BCMSWYSIWYGIFrameId).contentWindow.focus();
	}
	else
	{
		document.getElementById(BCMSTextEditorId).focus();
	}
}

function WYSIWYGCommand(aName, aArg)
{
 	WYSIWYGGetIFrameDocument(BCMSWYSIWYGIFrameId).execCommand(aName,false, aArg);
 	EditorFocus();
}

function WYSIWYGApplyText()
{
	WYSIWYGGetIFrameDocument(BCMSWYSIWYGIFrameId).body.innerHTML = document.getElementById(BCMSTextEditorId).value;
}

function WYSIWYGCommitText()
{
	var TextEditor = document.getElementById(BCMSTextEditorId);
	var TextEditorContent =  WYSIWYGGetIFrameDocument(BCMSWYSIWYGIFrameId).body.innerHTML;

	//some (x)html and image path conversions
	var imgURI = decodeURI(image_uri);
	//TextEditorContent = TextEditorContent.replace(/\.\.[\/]+Content\/images\//g, imgURI);
	TextEditorContent = TextEditorContent.replace(/<hr([^>\/]*)>/g, "<hr$1 />");
	TextEditorContent = TextEditorContent.replace(/<br([^>]*)>/g, "<br$1 />");
	TextEditorContent = TextEditorContent.replace(/<img([^>]*)>/g, "<img$1 />");
	TextEditorContent = TextEditorContent.replace(/<([^>]*)\/ \/>/g, "<$1/>");

	TextEditor.value = TextEditorContent;
}

/////////////////////
//WYSYIWYG: Mozilla//
/////////////////////

function WYSIWYGGetIFrameDocument(id)
{
	return document.getElementById(id).contentDocument;
}

//////////////////
//WYSYIWYG: MSIE//
//////////////////
/*

function WYSIWYGGetIFrameDocument(id)
{
	return document.frames[id].document;
}

*/

/////////////////
//notifications//
/////////////////

//fade out notification boxes
function BCMSHideNotifier(runTotal, runCurrent, beginFadeout, intervall)
{
	runCurrent = Math.min(runCurrent, runTotal);
	if(runCurrent < runTotal)
	{
		window.setTimeout("BCMSHideNotifier("+runTotal+","+(runCurrent+intervall)+","+beginFadeout+","+intervall+")",intervall);
	}
	//show timeout in #nfcTimeoutbarIndicator
	var len,notifier;
	if(runCurrent >= beginFadeout)
	{
		//fade stuff
		notifier = 	document.getElementById(BCMSNotifier);
		if(runTotal > runCurrent)
		{
			len = 100/(runTotal-beginFadeout)*(runCurrent-beginFadeout);
			notifier.style.opacity = (1.0 - len/100);
		}
		else
		{
			notifier.style.display = 'none';
		}
	}
}

function BCMSNotifierHide()
{
	var notifier = 	document.getElementById(BCMSNotifier);
	notifierOpacity -= 0.05;
 	if(notifierOpacity < 0)
 	{
		notifierOpacity = 0;
		notifier.style.display = 'none';
	}
 	if(notifierOpacity > 0)
 	{
 		window.setTimeout("BCMSNotifierHide()",100);
 	}
	notifier.style.opacity = notifierOpacity;
}
/////////////////
//ancient//
/////////////////
var doclientredraw = false;
var prevv = false;
var chk = false;
var update;
var check;
var userlist;
var checkintervall = 5000;
var updateintervall = 5000;
var editorSizeReduct = 250;
var ta_height = 400;
var ta_org_heigth;
var mx = 0;
var my = 0;
var req;
var userreq;
var chatreq;
var getuserlisturl;
var startx = 0;
var lastping;
var content_changed = true;
var notifierHeight = -1;
var notifierOpacity = 1.0;
var smallMessageOpacity = 1.0;
var ishtmlview = false;
var css = '';
var act = false;
var tog = false;
var wysiwygmode = false;

////////////////////////////////////////////////////////////////////////////////
// HTML WYSIWYG editor functions 
////////////////////////////////////////////////////////////////////////////////
function viewMode(className, flipimg)
{
	//management view flipping fx
	if(flipimg == undefined)
	{
		flipimg = true;
	}
	inputs = document.getElementsByTagName('input');
	var temp,oldclass;
	for(var i = 0; i < inputs.length; i++)//>
	{
		if(inputs[i].name.substr(0,7) == 'select_')
		{
			//inputs[i].checked = check;
			id = inputs[i].name.replace(/select_/, "");
			if(className != document.getElementById(id).className)
			{
				oldclass = document.getElementById(id).className;
				document.getElementById(id).className = className;
				if(flipimg && (oldclass == 'thumbnail' || className == 'thumbnail'))
				{
					temp = document.getElementById('img_'+id).src;
					document.getElementById('img_'+id).src = document.getElementById('img_'+id).alt;
					document.getElementById('img_'+id).alt = temp;
				}
			}
		}
	}
}

function body_load()
{
	if(document.getElementById("wysiwygeditor"))
		bWYSIWYGAllowed = true;
	BCMSInitialize();
}

//for login
function disableInputs()
{
	//login foo
	var inputs = document.getElementsByTagName('input');
	for(var i = 0; i < inputs.length; i++)//>
	{
		inputs[i].style.background = "#eeeeec";
		inputs[i].style.color = "#233436";
		inputs[i].style.border = "0px";
	}
}

function doRichEditCommand(aName, aArg)
{
 	WYSIWYGCommand(aName, aArg);
 	EditorFocus();
}

function applyTag(tag)
{
    var cursel = document.getElementById(tag).selectedIndex;
    if (cursel != 0) 
    {
        var selected = document.getElementById(tag).options[cursel].value;
        document.getElementById('wysiwygeditor').contentWindow.document.execCommand(tag, false, selected);
        document.getElementById(tag).selectedIndex = 0;
    }
    document.getElementById("wysiwygeditor").contentWindow.focus();
}

         
////////////////////////////////////////////////////////////////////////////////

function showElem(id){
    document.getElementById(id).style.visibility = 'visible';
    document.getElementById(id).style.display = 'block';
}

function hideElem(id){
    document.getElementById(id).style.visibility = 'hidden';
    document.getElementById(id).style.display = 'none';
}

function activate(){
    if(startx == 0){
        startx = mx;
        ta_org_heigth = ta_height;
    }
}
function deactivate(){
    startx = 0;
    document.getElementById('ta_size').value = ta_height;
}

function savepos(e){
    mx = e.pageY;
    if(startx != 0){
        ta_height = ta_org_heigth + (mx - startx);
    }
}

function fademessage(msg){
    var msgElem =  document.getElementById('js_message');
    msgElem.innerHTML = msg;
    msgElem.style.opacity = 1.0;
    setTimeout("fade_out()", 5000);
}

function fade_out(){
	if(smallMessageOpacity >= 0)
	{
		smallMessageOpacity -= 0.05;
		document.getElementById('js_message').style.opacity = smallMessageOpacity;
		setTimeout("fade_out()", 30);
	}
	else
	{
		document.getElementById('js_message').innerHTML = '';
		smallMessageOpacity = 1.0;
	}
}

function set_js_msg_color(bg, color){
    var elem =  document.getElementById('js_message');
    elem.style.background = bg;
    elem.style.color = color;
}

function actv(){
    somethinghappened = true;
}
