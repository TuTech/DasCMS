if(!org){var org = {};}
org.bambuscms = (function(){
	var _execStack = function(stack){for(var i = 0; i < stack.length; i++){if(typeof stack[i] == 'function'){stack[i]();}}},
		_loadStack = [],
		_unloadStack = [];
	return {
		bodyLoad: function(){_execStack(_loadStack);},
		bodyUnLoad: function(){_execStack(_unloadStack);},
		addToBodyLoad: function(fx){_loadStack.push(fx)},
		addToBodyUnLoad: function(fx){_unloadStack.push(fx)}
	};
})();
(function(){
	var l = org.bambuscms.bodyLoad,
		u =	org.bambuscms.bodyUnLoad;
	if(window.addEventListener)
		{window.addEventListener('load', l, false);window.addEventListener('unload', u, false);}
	else if(window.attachEvent)
		{window.attachEvent('onload', l);window.attachEvent('onunload', u);}
	else
		{window.onload = l;window.onunload = u;}
})();

function $(id){return document.getElementById(id);}
function $c(tag){return document.createElement(tag);}
function $t(text){return document.createTextNode(text.replace(/\_/g,'_\u00AD'));}