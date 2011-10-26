CMS.Document = {
	_setFormAction:function(newAction){
		this._setFormData('_action', newAction);
	},
	
	_setFormData:function(key, value){
		var existing = $('input[name=' + key + ']');
		if(existing.length){
			existing.attr('value', value);
		}
		else{
			$("#documentform").append(
				$('<input type="hidden">').attr({
					name: key,
					value: value
				})
			);
		}
	},
	
	_submitForm: function(action, data){
		data = data || null;

		//replace form data with custom data
		if(action && data){
			$("#documentform").html('');
			for(name in data){
				if(data.hasOwnProperty(name)){
					this._setFormData(name, data[name]);
				}
			}
		}
		//change action
		if(action){
			this._setFormAction(action);
		}
		
		//submit form
		$("#documentform").addClass("form-submitted").submit();
	},
	
	create: function(){
		var title = prompt(CMS.translate("title_of_new_content"), CMS.translate("new_content")),
			useCurrentAsTemplate = false;
		
		//require title for new content
		if(title){
			if(useCurrentAsTemplate){
				this._setFormData('create', title);
				this._submitForm('create');
			}else{
				this._submitForm('create', {create: title});
			}
		}
	},
	
	destroy: function(){
		if(confirm(CMS.translate('delete_content?'))){
			//create "delete" form and submit it
			this._submitForm('delete', {});
		}
	},
	open: function(alias){
		if(alias){
			top.location.search = "?editor=" + $('#document-form-app').attr('value') + "&edit=" + alias;
		}else{
			CMS.OpenDialog.show();
		}
	},
	save: function(){
		this._submitForm('save');
	}
};