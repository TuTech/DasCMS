//COMPAT
var org = {bambuscms: {editor: {wysiwyg: {}}, wsidebar: {show: function(){}}, wopenfiledialog: {prepareLinks: function(){}}, app: {document: {}, hotkeys:{register: function(){}}}}};


var CMS = {};

$(document).keydown(function(event){
	var key, target;
	if(!event.ctrlKey)return;

	key = String.fromCharCode(event.keyCode);
	key = event.shiftKey ? key.toUpperCase() : key.toLowerCase();
	target = $("#App-Hotkey-CTRL-" + key);

	if(target.length){
		$(target[0]).click();
	}
});

CMS.Document = {
	create: function(){
		
	},
	open: function(){
		
	},
	
	destroy: function(){
		
	},
	create: function(){
		
	}
	
	//cms doc attrs
};

CMS.translate = function(phrase, options){
	options = options || {};
	
	var translation = phrase;
	
	if(org.bambuscms.localization[phrase]){
		translation = org.bambuscms.localization.phrase;
		
		for(key in options){
			if(options.hasOwnProperty(key)){
				replace = "/{{" + key + "}}/";
				
				//TODO Regexp compile and replacing
				
			}
		}
	}
	
	return translation;
}

CMS.Sidebar = {
	_getSidebarForSelector: function(element){
		return $('#' + element.id.replace(/WSidebar-selector-/, "WSidebar-child-"));
	},
	
	switchTab:function(event){
		var oldElm, newElm;
		newElm = event.currentTarget;
		oldElm = $("#WSidebar-select .selectedWidget")[0];

		if(newElm.id == oldElm.id)return;

		oldElm.className = "";
		newElm.className = "selectedWidget";

		this._getSidebarForSelector(oldElm).css("display", 'none');
		this._getSidebarForSelector(newElm).css("display", 'block');
	},
	
	//static handler function
	switch_tab: function(event){
		return CMS.Sidebar.switchTab(event);
	}
};

$(function(){
	$('#WSidebar-select span').click(CMS.Sidebar.switch_tab);
});


CMS.Notification = {};

CMS.Dialog = {
	SRC: "Management/Dialogs/",
	
	_callback: null,
	_form: null,
	
	
	_informCallback: function(msg, params){
		params = params || {};

		if(this._callback && this._callback[msg]){
			this._callback.msg(params);
		}
	},
	
	_close:function(){
		
	},
	
	isActive:function(){},
	
	open: function(template, callback){
		if(!callback)callback = null;
		this._callback = callback;
		
		if(this.isActive()){
			this._informCallback('dialogDenied');
			return;
		}
		//load template via ajax
		//jquery ajax foo
		var self = this;
		$.get(this.SRC + template + '.html', function(data) {
			self.templateDidLoad(data);
		});
		
		//replace translations
		//replace all .translate and use id as key
		//
		//$('#_cms_document_form .translate').each(function(i, element){
		//	element.innerHTML = CMS.translate(element.id);
		//});
		
		//callback handling
		//this._informCallback('formActivated');
	},
	
	templateDidLoad: function(data){
		$("#dialogues").html(data).removeClass('hide');
		this._informCallback('formActivated');
	},
	
	close:function(){
		this._close();
		this._informCallback('formClosed');
	},
	
	cancel:function(){
		this._close();
		this._informCallback('formCanceled');
	}
};

//create form with GET[_action]





