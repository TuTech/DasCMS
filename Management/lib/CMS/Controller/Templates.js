CMS.Templates = {
	SRC: "Management/Dialogs/",

	//cache loaded templates
	_cache:{},
	
	//internal ajax callback handler
	_loaded: function(template, data, callback){
		this._cache[template] = data;
		this._informCallback(callback, template);
	},
	
	//inform external callback function or object
	_informCallback:function(callback, template){
		//function callback
		if(typeof callback == "function"){
			callback(template, this._cache[template]);
		}
		//object callback
		else if(callback.templateDidLoad && typeof callback.templateDidLoad == "function"){
			callback.templateDidLoad(template, this._cache[template]);
		}
		//no callback
		else throw "Broken callback";
	},
	
	//called from extern to load template
	//callback may be function or object with "templateDidLoad" method
	load: function(template, callback){
		var self = this;
		if(!this._cache[template]){
			$.get(this.SRC + template + '.html', function(data) {
				self._loaded(template, data, callback);
			});			
		}
		else this._informCallback(callback, template);
	},
	
	parse: function(template, data){
		var rex;
		for(key in data){
			rex = new RegExp("{{" + key + "}}", "ig");
			template = template.replace(rex, data[key]);
		}
		return template;
	}
};