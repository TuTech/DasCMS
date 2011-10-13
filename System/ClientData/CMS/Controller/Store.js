CMS.Store = ({
	SRC_URL: 'Management/ajaxhandler.php?call=provideOpenDialogData&controller=',
	
	_loaded:false,
	_loadCallbacks: [],
	_store:[],
	_indexes: {
		alias:{},
		tags: {}
	},
	
	load:function(){
		var src = $('#document-form-data-source').attr('value'),
			self = this;
		$.getJSON(
			this.SRC_URL + src, 
			function(data){self._contentsDidLoad(data);}
		);
	},
	
	init:function(){
		var self = this;
		$(function(){self.load();});
		return this;
	},
	
	didFinishLoading: function(callback){
		if(!callback){
			return this._loaded;
		}
		if(this._loaded){
			this._informCallback(callback);
		}
		else{
			this._loadCallbacks.push(callback);
		}
	},
	
	_informCallback: function(callback){
		if(callback && callback.storeDidLoad){
			callback.storeDidLoad(this);
		}
	},
	
	_contentsDidLoad:function(data){
		var BC = CMS.Model.BasicContent,
			model, 
			index,
			items = data.items;
		BC.setAttrMap(data.itemMap);
		for(i in items){
			model = BC.create(items[i]);
			index = this._store.push(model);
			model.ref = index;
			this._indexes.alias[model.alias] = index;
			//TODO: tag index
		}
		
		this._loaded = true;
		for(i in this._loadCallbacks){
			this._informCallback(this._loadCallbacks[i]);
		}
	},
	
	each: function(cb){
		for(i in this._store){
			cb(i, this._store[i]);
		}
	},
	
	get:function(){},
	set:function(){}
}).init();
//
//{
//	"title":"\u00d6ffnen",
//	"nrOfItems":30,
//	"iconMap":[".\/System\/ClientData\/Icons\/tango\/large\/mimetypes\/CPage.png"],
//	"smallIconMap":[".\/System\/ClientData\/Icons\/tango\/extra-small\/mimetypes\/CPage.png"],
//	"itemMap":{"title":0,"alias":1,"icon":2,"pubDate":3,"size":4},
//	"sortable":{"title":"title","pubDate":"pubDate"},
//	"items":
//}