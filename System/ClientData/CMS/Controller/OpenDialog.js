CMS.OpenDialog = ({	
	TEMPLATE: "open_content",
	ITEM_TEMPLATE: "open_content_item",
	
	_status:{
		tpl:	 false,
		item_tpl:false,
		store:	 false,
		ok: function(){return this.tpl && this.item_tpl && this.store}
	},
	_showing: false,
	_locked: false,
	_store: null,
	_itemTpl: "",
	_hidden: [],
	
	toggle:function(){
		if(this._showing){
			this.hide()
		}
		else{
			this.show();
		}
	},
	
	lock: function(){
		this._locked = true;
		$('#dialog-opencontent').addClass("locked");
	},
	
	isLocked:function(){
		return this._locked;
	},
	
	show:function(){
		if(this._showing)return;
		this._showing = true;
		CMS.Templates.load(this.TEMPLATE,this);
		CMS.Templates.load(this.ITEM_TEMPLATE,this);
		$("#documentform").hide();
	},
	
	hide:function(){
		if(!this._showing || this._locked)return;
		this._showing = false;
		$("#documentform").show();
		CMS.Dialog.close();
	},
	
	_clickSource:function(event, callback){
		var source = null;
		if($(event.target).data('alias')){
			source = event.target;
		}
		else if($(event.target.parentNode).data('alias')){
			source = event.target.parentNode;
		}
		if(source && callback){
			callback(source);
		}
		return source;
	},
	
	templateDidLoad:function(name, template){
		var self = this;
		if(name == this.TEMPLATE){
			CMS.Dialog.show(template, this);
			this._status.tpl = true;
			$('#dialog-opencontent').addClass('loading');
		}
		else if(name == this.ITEM_TEMPLATE){
			this._status.item_tpl = true;
			this._itemTpl = template;
		}
		$('#dialog-opencontent').unbind('click').click(function(event){
			self._clickSource(event, function(source){
				$(source).toggleClass("selected");
			});
		});		
		$('#dialog-opencontent').dblclick(function(event){
			self._clickSource(event, function(source){
				self.openContent($(source).data('alias'));
			});
		});
		$('#dialog-opencontent-search').keyup(function(event){
			var filter = event.target.value,
				alias = $(event.target).data('instant-open');
			if(event.which == 13 && alias){
				self.openContent(alias);
			}
			self.filterItems({title: filter});
		});
		this._fillDialog();
	},
	
	filterItems:function(filters){
		var titleRex = new RegExp(filters.title || '', 'gi'),
			self = this,
			match = {count:0,alias:''},
			sbox = $('#dialog-opencontent-search');
		$('#dialog-opencontent').addClass('loading');
		//apply filter
		CMS.Store.each(function(i, item){
			if(item.title.match(titleRex)){
				match.count++;
				match.alias = item.alias;		
				self._hidden[i] = false;
			}
			else{
				self._hidden[i] = true;
			}

		});
		if(match.count == 1){
			sbox.data('instant-open', match.alias);
			sbox.addClass('instant-open');
		}
		else{
			sbox.data('instant-open', '');
			sbox.removeClass('instant-open');
		}
		//redraw contents
		this._fillDialog({instantOpen: (match.count == 1)});
	},
	
	openContent:function(alias){
		CMS.Document.open(alias);
	},
	
	_fillDialog:function(opt){
		opt = opt || {};
		//check if dialog should display and does not display, tpl, item-tpl & store are present 
		if(this._status.ok()){
			var target = $('#dialog-opencontent-body'),
				template = this._itemTpl,
				items = [],
				self = this;
			if(opt.instantOpen){
				target.addClass('instant-open');
			}
			else{
				target.removeClass('instant-open');
			}
			CMS.Store.each(function(i, item){
				if(!(i in self._hidden) || !self._hidden[i]){
					items.push(CMS.Templates.parse(template, item));
				}
			});
			
			target.html(items.join("\n"));
			$('#dialog-opencontent-search').focus();
			$('#dialog-opencontent').removeClass('loading');
		}
	},
	
	select:function(){},
	query:function(){},
	
	//callbacks
	formDidShow:function(){
		//load contents via ajax if not in cache
	},
	
	storeDidLoad: function(store){
		this._store = store;
		this._status.store = true;
		this._fillDialog();
	},
	
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
	}
}).init();