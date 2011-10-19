CMS.OpenDialog = ({	
	TEMPLATE: "open_content",
	ITEM_TEMPLATE: "open_content_item",
	
	_status:{
		tpl:	 false,
		item_tpl:false,
		store:	 false,
		ok: function(){return this.tpl && this.item_tpl && this.store}
	},
	_tpl: {},
	_showing: false,
	_locked: false,
	_store: null,
	_hidden: [],
	_dataSource: null,
	_view: null,

	init: function(){
		var self = this;
		$(function(){
			var elm = $('#document-form-content');
			//elements exists and has empty value
			if(elm.length && !elm.attr('value')){
				self.show();
				self.lock();
			}
			CMS.Store.didFinishLoading(self);
		});
		return this;
	},

	//
	// API
	//
	
	//show dialog
	show:function(){
		if(!this._view){
			this._view = CMS.OpenDialogView.init(this);
			CMS.Templates.load(this.TEMPLATE,this);
			CMS.Templates.load(this.ITEM_TEMPLATE,this);
		}
		else{
			this._view.show();
		}
	},

	//diable dialog closing
	lock: function(){
		this._locked = true;
		this._view.lock();
	},
	
	//hide dialog
	hide:function(){
		if(!this._view.isShowing() || this._locked)return;
		this._view.hide();
	},
	
	//
	// TEMPLATE AND STORE CALLBACKS
	//
	
	//teplate load callback
	templateDidLoad:function(name, template){
		if(name == this.TEMPLATE){
			this._status.tpl = true;
		}
		else if(name == this.ITEM_TEMPLATE){
			this._status.item_tpl = true;
		}
		this._tpl[name] = template;
		this.componentDidLoad();
	},
	
	//store load callback
	storeDidLoad: function(store){
		this._store = store;
		this._status.store = true;
		this._dataSource = store.filter({title:''});
		this.componentDidLoad();
	},
	
	//load view if all did load
	componentDidLoad: function(){
		if(this._status.ok()){
			this._view.show();
		}
	},
	
	//
	// VIEW REQUESTS AND CALLBACKS
	//
	
	//view requests main template
	getMainTemplate:function(){
		return this._tpl[this.TEMPLATE];
	},
	
	//view requests item template
	getItemTemplate:function(){
		return this._tpl[this.ITEM_TEMPLATE];
	},
	
	didClickItem:function(ref){
		this._view.toggleItemSelection(ref);
	},
	
	didDoubleClickItem:function(ref){
		//open content with ref
		var item = this._store.get(ref);
		CMS.Document.open(item.alias);
	},
	
	didChangeFilter:function(value){
		//update datasource
		this._dataSource = this._store.filter({title: value});
		
		if(this.getItemCount() == 1){
			this._view.highlightItems([this.getItemByNr(0).ref]);
		}
		else{
			this._view.highlightItems([]);
		}
		
		//update view
		this._view.contentsDidChange();
	},
	
	//open 
	didCommitFilter: function(value){
		if(this._dataSource.getItemCount() == 1){
			this._dataSource.each(function(i, item){
				CMS.Document.open(item.alias);
			});
		}
	},
	
	viewDidShow:function(view){},
	
	getItemCount: function(){
		return this._dataSource.getItemCount();
	},
	
	getItemByNr: function(nr){
		return this._dataSource.getNr(nr);
	},
	
	getItemByRef: function(ref){
		return this._dataSource.get(ref);
	},
	
	each: function(callabck, from, length){
		from = from || 0;
		to = to || this.getItemCount();
		var item;
		for(var i = from; i < from + length; i++){
			item = this._dataSource.get(i);
			if(item){
				callabck(i, item);
			}
		}
	}
}).init();