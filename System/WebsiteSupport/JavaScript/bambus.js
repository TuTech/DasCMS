if(!org){var org = {};}
if(!org.bambuscms){
org.bambuscms = {};
org.bambuscms._execStack = function(stack){for(var i = 0; i < stack.length; i++){if(typeof stack[i] == 'function'){stack[i]();}}};

org.bambuscms._loadStack =[];
org.bambuscms.bodyLoad = function(){org.bambuscms._execStack(org.bambuscms._loadStack);};
org.bambuscms.addToBodyLoad = function(fx){org.bambuscms._loadStack.push(fx)};

org.bambuscms._unloadStack =[];
org.bambuscms.bodyUnLoad = function(){org.bambuscms._execStack(org.bambuscms._unloadStack);};
org.bambuscms.addToBodyUnLoad = function(fx){org.bambuscms._unloadStack.push(fx)};

(function(){
if(window.addEventListener)
{window.addEventListener('load', org.bambuscms.bodyLoad, false);
window.addEventListener('unload', org.bambuscms.bodyUnLoad, false);}
else if(window.attachEvent)
{window.attachEvent('onload', org.bambuscms.bodyLoad);
window.attachEvent('onunload', org.bambuscms.bodyUnLoad);}
else
{window.onload = org.bambuscms.bodyUnLoad;
window.onunload = org.bambuscms.bodyUnLoad;}
})();
}