var CMS = {
	_localization: {},
	_hotKeyLookup: {},
	
	handleHotKey: function(key){
		if(this._hotKeyLookup[key]){
			this._hotKeyLookup[key]();
			return true;
		}
		return false;
	},
	
	Model: {
		_createMappedModel:function(data, map, converter){
			converter = converter || {};
			var model = {}, 
				defCon = function(data){return data;}, 
				conv;
				
			for(attr in map){
				if(map.hasOwnProperty(attr)){
					conv = converter[attr] || defCon;
					model[attr] = conv(data[map[attr]]);
				}
			}
			return model;
		}
	},
	
	extend: function(constructor, obj){
		constructor.prototype = obj;
		return constructor;
	}
};

//translation functionality
CMS.translate = function(phrase, options){
	options = options || {};
	
	var translation = phrase;
	
	if(CMS._localization[phrase]){
		translation = CMS._localization.phrase;
		
		for(key in options){
			if(options.hasOwnProperty(key)){
				replace = "/{{" + key + "}}/";
				
				//TODO Regexp compile and replacing
				
			}
		}
	}
	
	return translation;
};

//grab hotkeys
$(document).keydown(function(event){
	var key, target;
	if(!event.ctrlKey)return;

	key = String.fromCharCode(event.keyCode);
	key = event.shiftKey ? key.toUpperCase() : key.toLowerCase();
	
	if(CMS.handleHotKey(key)){
		console.log("canceling event");
		event.preventDefault();
		event.stopPropagation();
		return false;
	}
	return true;
});













