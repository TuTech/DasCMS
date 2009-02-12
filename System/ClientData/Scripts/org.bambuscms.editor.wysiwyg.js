org.bambuscms.editor.wysiwyg = {};
org.bambuscms.editor.wysiwyg._editor = function(frame){
	//the document
	this._doc = null;
	//the iframe
	this._target = frame;
	//execute rich text command
	this.exec =function(cmd, arg){
		arg = (arg == undefined) ? null : arg;
		this._doc.execCommand(cmd, false, arg);
		this._target.contentWindow.focus();
	};
	//enablke wysiwyg (must be called on body.onload)
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
	//set the html content for the wysiwyg editor
	this.setText = function(text){
		this._doc.body.innerHTML = text;
	};
	//get the html from the wysiwyg editor
	this.getText = function(){
		return this._doc.body.innerHTML;
	};
};

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
		var cmdBar = document.createElement('div');
		cmdBar.className = 'org_bambuscms_editor_wysiwyg_commandBar';
		for(func in commands)
		{
			var trigger = function(){_me.butClick(this.title);};
			var but = document.createElement('button');
			but.onclick = trigger;
			but.title = func;
			but.appendChild(document.createTextNode(commands[func]));
			cmdBar.appendChild(but);
		}
		this.elements.outer.insertBefore(cmdBar, this.elements.inner);
		return cmdBar;
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
		this.wrapper.exec('styleWithCSS',false);
		this.wrapper.setText(this.elements.source.value);
	};
}

org.bambuscms.editor.wysiwyg.create = function(textarea)
{
	//get ref to textarea as source for our content
	textarea = (typeof textarea == 'string') ? $(textarea) : textarea;
	//container for all our html elements
	var elements = {
		'outer':document.createElement('div'),
		'inner':document.createElement('div'),
		'editor':document.createElement('iframe'),
		'source':textarea
	};
	//build dom tree an insert before the textarea
	elements.outer.appendChild(elements.inner);
	elements.inner.appendChild(elements.editor);
	elements.source.parentNode.insertBefore(elements.outer,elements.source);
	//give all our elements css classes for a nice design
	for(elm in elements)
	{
		elements[elm].className = 'org_bambuscms_editor_wysiwyg_'+elm;
	}
	//the wrapper will care for the command execution and browser compatibility
	var wrapper = new org.bambuscms.editor.wysiwyg._editor(elements.editor);
	//reutrn interface object
	var editor = new org.bambuscms.editor.wysiwyg._object(elements, wrapper);
	//activate wysiwyg ability
	org.bambuscms.autorun.register(function(){editor.start();});
	editor.buildToolbar({'bold':'B','italic':'i','underline':'u','strikethrough':'s','superscript':'sup','subscript':'sub'});
	return editor;
}