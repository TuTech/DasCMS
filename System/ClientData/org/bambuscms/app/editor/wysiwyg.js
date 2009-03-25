org.bambuscms.editor.wysiwyg = {};
org.bambuscms.editor.wysiwyg._index = 0;
org.bambuscms.editor.wysiwyg.editors = [];
org.bambuscms.editor.wysiwyg._editor = function(frame){
	//the document
	this._doc = null;
	//the iframe
	this._target = frame;
	//execute rich text command
	this.exec =function(cmd, arg){
		if(arg == undefined)
		{
			try{
				this._doc.execCommand(cmd, false);
			}
			catch (e) {
				// ignore failed wysiwyg
			}
		}
		else
			this._doc.execCommand(cmd, false, arg);
		this._target.contentWindow.focus();
	};
	this.wysiwygOn = true;
	//enable wysiwyg (must be called on body.onload)
	this.makeEditable = function(){
		if(this._target.contentWindow)
		{
			this._doc = this._target.contentWindow.document;
			this._doc.designMode = "on";
		}
		else
		{
			this._doc = this._target.document;
			this._doc.contentEditable = 'true';
		}
	};
	this.switchWYSIWYG = function()
	{
		if(this.wysiwygOn)
		{
			var source = this._doc.body.innerHTML;
			this._doc.body.innerHTML = '';
			this._doc.body.appendChild(document.createTextNode(source));
			this._doc.body.style.whiteSpace = 'pre';
		}
		else
		{
			var source = this._doc.body.firstChild.nodeValue;
			this._doc.body.innerHTML = source;
			this._doc.body.style.whiteSpace = '';
		}
		this.wysiwygOn = !this.wysiwygOn;
		$('WWYSIWYGPanel-Design').style.display = this.wysiwygOn ? 'block' : 'none';
		$('WWYSIWYGPanel-Code').style.display = this.wysiwygOn ? 'none' : 'block';
		return this.wysiwygOn;
	}
	//set the html content for the wysiwyg editor
	this.setText = function(text){
		this._doc.body.innerHTML = text;
	};
	//get the html from the wysiwyg editor
	this.getText = function(){
		return this._doc.body.innerHTML;
	};
};
org.bambuscms.editor.wysiwyg.commitAll = function()
{
	for(var i = 0; i < org.bambuscms.editor.wysiwyg.editors.length; i++)
	{
		if(org.bambuscms.editor.wysiwyg.editors[i].commit)
		{
			org.bambuscms.editor.wysiwyg.editors[i].commit();
		}
	}
}
//org.bambuscms.autorun.register(org.bambuscms.editor.wysiwyg.activateWrapper);
org.bambuscms.editor.wysiwyg._object = function(elements, wrapper)
{
	this.elements = elements;
	this.wrapper = wrapper;
	
	var _wrap = this.wrapper;
	var _me = this;
	//build a div with buttons executing whatever defined in the commands object
	//elementObject = {functionName:icon,..}
	this.buildToolbar = function(commands){
		var cmdBar = $c('div');
		cmdBar.className = 'org_bambuscms_editor_wysiwyg_commandBar';
		for(func in commands)
		{
			var trigger = function(){_me.butClick(this.title);};
			var but = $c('img');
			but.src = 'System/ClientData/Icons/22x22/actions/format-'+commands[func]+'.png';
			but.onclick = trigger;
			but.title = func;
			//but.appendChild(document.createTextNode(commands[func]));
			cmdBar.appendChild(but);
		}
		this.elements.outer.insertBefore(cmdBar, this.elements.inner);
		return cmdBar;
	};

	this.exec = function(cmd, arg)
	{
		switch(cmd.toLowerCase())
		{
			case 'createlink':
				arg = arg || prompt(_('link_please'), 'http://');
			default:
				_wrap.exec(cmd, arg);
		}
	};
	
	this.commit = function()
	{
		_me.elements.source.value = _wrap.getText();
	}
	this.disableWYSIWYG = function(){
		
	}
	
	this.switchWYSIWYG = function()
	{
		return _wrap.switchWYSIWYG();
	}
	//get the html from the wysiwyg editor
	this.getText = function(){
		return _wrap.getText();
	};
	
	//button in command bar clicked? 
	//read the action from title and tell the wrapper to execute it
	this.butClick = function(action){
		_wrap.exec(action);
	};
	
	this.format = function(tag){};
	
	this.colorForeground = function(color){};
	this.colorBackground = function(color){};
	
	this.start = function(){
		this.wrapper.makeEditable();
		if(navigator.userAgent.indexOf('Gecko') >= 0)
			this.wrapper.exec('styleWithCSS',false);
		this.wrapper.setText(this.elements.source.value);
	};
}

org.bambuscms.editor.wysiwyg.create = function(textarea, fillScreen)
{
	var myIndex = ++org.bambuscms.editor.wysiwyg._index;
	//get ref to textarea as source for our content
	textarea = (typeof textarea == 'string') ? $(textarea) : textarea;
	//container for all our html elements
	var elements = {
		'outer':$c('div'),
		'inner':$c('div'),
		'editor':$c('iframe'),
		'source':textarea
	};
	//build dom tree an insert before the textarea
	elements.outer.appendChild(elements.inner);
	elements.inner.appendChild(elements.editor);
	if(elements.source.nextSibling)
	{
		elements.source.parentNode.insertBefore(elements.outer,elements.source.nextSibling);
	}
	else
	{
		elements.source.parentNode.appendChild(elements.outer);
	}
	
	//give all our elements css classes for a nice design
	for(elm in elements)
	{
		elements[elm].className += ' org_bambuscms_editor_wysiwyg_'+elm;
		if(!elements[elm].id)
		{
			elements[elm].id = 'org_bambuscms_editor_wysiwyg_'+elm+'_'+myIndex;
		}
	}
	//register in autosize as source
	if(fillScreen)
	{
    	var h = -190;
        if(elements.editor.offsetTop)
        {
    		h = function(){return (elements.editor.offsetTop+5)*-1;};
    	}
		org.bambuscms.display.setAutosize(elements.editor.id, 0, h, true);
	}

	//the wrapper will care for the command execution and browser compatibility
	var wrapper = new org.bambuscms.editor.wysiwyg._editor(elements.editor);
	//reutrn interface object
	var editor = new org.bambuscms.editor.wysiwyg._object(elements, wrapper);
	//activate wysiwyg ability
	org.bambuscms.autorun.register(function(){editor.start();});
	org.bambuscms.editor.wysiwyg.editors[org.bambuscms.editor.wysiwyg.editors.length] = editor;
	//editor.buildToolbar({'bold':'bold','italic':'italic','underline':'underline','strikethrough':'strike','superscript':'sup','subscript':'sub'});
	return editor;
}