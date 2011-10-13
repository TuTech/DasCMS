CMS.Dialog = {
	ID: "#dialogues",
	CALLBACKS:{
		FAIL:  'formDidFail',
		SHOW:  'formDidShow',
		CLOSE: 'formDidClose',
		CANCEL:'formDidCancel'
	},
	
	_callback: null,
	_form: null,
	
	//send a message to the callback object
	_informCallback: function(msg, params){
		params = params || {};

		if(this._callback && this._callback[msg]){
			this._callback[msg](params);
		}
	},
	
	//check if a dialog is present
	isActive:function(){
		return !$(this.ID).hasClass('hide');
	},
	
	//show a dialog
	show: function(form, callback){
		var oldCallback = this._callback;
		this._callback = callback || null;

		if(this.isActive()){
			this._informCallback(this.CALLBACKS.FAIL);
			this._callback = oldCallback;
			
			return;
		}
		
		this._form = form;

		$(this.ID).html(form);
		//add form close/cancel logic
		
		$(this.ID).removeClass('hide');
		this._informCallback(this.CALLBACKS.SHOW);
	},
	
	//remove current dialog
	_close:function(){
		$(this.ID).html("");
		$(this.ID).addClass('hide');
	},
	
	//close dialog normally
	close:function(){
		this._close();
		this._informCallback(this.CALLBACKS.CLOSE);
	},
	
	//close dialog abnormally
	cancel:function(){
		this._close();
		this._informCallback(this.CALLBACKS.CANCEL);
	}
};