var accesskeyhidetimeout;
var hotkeytimeout;
var hotkeyfunction	= new Array();
/*Hotkeys (taskbar buttons)*/
var NULL = undefined;
function BCMSInitializeHotKeys()
{/*
	document.onkeydown = function(e){
		if (!e)
			e = window.event;
		if (e.which) {
			var keycode = e.which;
		} 
		else if (e.keyCode) {
			keycode = e.keyCode;
		}
		if(keycode == 18 /*ALT* /){
			showAccessKeys();
			hideHotKeys();
		}
		if(keycode == 17/*STRG* /)
		{
			showHotKeys();
			hideAccessKeys();
		}
		if(e.which == 9/*TAB* /)
        {
            insertText('    ');
            return false;
        }
	}
	document.onkeyup = function(e){
		if (!e)
			e = window.event;
		if (e.which) {
			var keycode = e.which;
		} 
		else if (e.keyCode) {
			keycode = e.keyCode;
		}
		if(keycode == 18 /*ALT* /){
			hideAccessKeys();
		}
		if(keycode == 17 /*STRG* /)
		{
			hideHotKeys();
		}
	}	
	document.onkeypress = function(e){
        var bform = document.getElementById('documentform');
        if(bform && e.ctrlKey)
        {
        	return execHotKey(e.which);
        }
	}*/
}

function addHotKeyListener(keyCode, functionname)
{/*
	if(!hasHotKeyListener(keyCode))
	{
		hotkeyfunction[keyCode] = functionname;
	}
*/}
function removeHotKeyListener(keyCode)
{
	/*if(hasHotKeyListener(keyCode))
	{
		//remove
		hotkeyfunction[keyCode] = NULL;
	}*/
}
function hasHotKeyListener(keyCode)
{
	//return (hotkeyfunction[keyCode] != NULL)
}
function execHotKey(keyCode)
{
	/*if(hasHotKeyListener(keyCode))
	{
		eval(hotkeyfunction[keyCode]);
		return false;
	}*/
//	alert(keyCode+' has no assigned function');
}
function showHotKeys()
{
/*	document.getElementById('taskbar').className = '';
	hotkeytimeout = window.setTimeout("hideHotKeys()", 5000);
*/}
function hideHotKeys()
{/*
	window.clearTimeout(hotkeytimeout);
	document.getElementById('taskbar').className = 'nohotkeys';
*/}

/*Accesskeys editor tabs*/
function showAccessKeys()
{
	//  accesskeyhidetimeout = window.setTimeout("hideAccessKeys()", 5000);
}
function hideAccessKeys()
{
//	window.clearTimeout(accesskeyhidetimeout);
}