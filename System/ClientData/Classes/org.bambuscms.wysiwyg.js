//needs:
//org.js
//org.bambuscms.js

org.bambus.wysiwyg = {
	bold:"bold",
	italic: "italic",
	strike: "strikeThrough",
	sub:"subscript",
	sup:"superscript",
	underline: "underline",
	
	
	center: "justifyCenter",
	left:"justifyLeft",
	right:"justifyRight"
};
org.bambus.wysiwyg.setBackground = function(){}//backColor
org.bambus.wysiwyg.setForeground = function(){}//foreColor

org.bambus.wysiwyg.format = function(){}//--object vars/removeFormat
org.bambus.wysiwyg.align = function(){}//--object vars/removeFormat

org.bambus.wysiwyg.makeBlock = function(){}//formatBlock: H1 - H6, ADDRESS, and PRE
org.bambus.wysiwyg.makeHeading = function(){}//heading: H1 - H6(no ie)

org.bambus.wysiwyg.indent = function(){}//indent
org.bambus.wysiwyg.outdent = function(){}//outdent
org.bambus.wysiwyg.unorderedList = function(){}//insertUnorderedList
org.bambus.wysiwyg.orderedList = function(){}//insertOrderedList

org.bambus.wysiwyg.insertHTML = function(){}//insertHTML (no ie)
org.bambus.wysiwyg.insertImage = function(){}//insertImage
org.bambus.wysiwyg.insertHorizontalLine = function(){}//insertImage

org.bambus.wysiwyg.linkTo = function(){}//createLink
org.bambus.wysiwyg.removeLink = function(){}//unlink

org.bambus.wysiwyg.copy = function(){}//copy
org.bambus.wysiwyg.cut = function(){}
org.bambus.wysiwyg.paste = function(){}//paste

org.bambus.wysiwyg.undo = function(){}//undo
org.bambus.wysiwyg.redo = function(){}//redo


//fx make wysiwyg: takes existing element id
//writes at current pos <iframe id="wysblah"

org.bambus.wysiwyg.startWYSIWYG = function()
{
	//create iframe, write to iframe doc:
	/*
<body onload="load()">

JavaScript:
function load(){
  getIFrameDocument("editorWindow").designMode = "On";
}


function getIFrameDocument(aID){
  // if contentDocument exists, W3C compliant (Mozilla)
  if (document.getElementById(aID).contentDocument){
    return document.getElementById(aID).contentDocument;
  } else {
    // IE
    return document.frames[aID].document;
  }
}


HTML:
<button onclick="doRichEditCommand('bold')" style="font-weight:bold;">B</button>

JavaScript:
function doRichEditCommand(aName, aArg){
  getIFrameDocument('editorWindow').execCommand(aName,false, aArg);
  document.getElementById('editorWindow').contentWindow.focus()
}


	
	*/ 
}



