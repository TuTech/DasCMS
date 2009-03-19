function Create()
{
	input = document.createElement('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	org.bambuscms.app.dialog.create(_('create_new_template'), _('name_of_new_template'), input, _('ok'), _('cancel'));
	org.bambuscms.app.dialog.setAction('create');
	input.focus();
}
function Delete()
{
	input = document.createElement('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	org.bambuscms.app.dialog.create(_('delete_template'), _('do_you_really_want_to_delete_this_text'), input, _('yes'), _('no'));
	org.bambuscms.app.dialog.setAction('delete');
}
org.bambuscms.app.persons = {};
org.bambuscms.app.persons.attributes = {
	'phone':{
		'contexts': ['arbeit', 'privat','mobil','fax arbeit', 'fax privat'],
		'type':'phone',
		'values':{
			'arbeit':'+49 01054 2540 4521',
			'privat':'23904 555-7530'
		}
	},
	'im':{
		'contexts': ['aim', 'jabber','icq'],
		'type':'text',
		'values':{
			'aim':'dingsda',
			'jabber':'bumsta@foo.da'
		}
	}
};

org.bambuscms.app.persons.controller = {
	'shown':{},
	'addAttribute':function(attribute, type)
	{
		if(!org.bambuscms.app.persons.controller.shown[attribute])
		{
			org.bambuscms.app.persons.attributes[attribute] = {
				'contexts':[],
				'type':type,
				'values':{}
			};
			org.bambuscms.app.persons.controller.shown[attribute] = 1;
			//add to gui
			org.bambuscms.app.persons.gui.createAttribute(attribute, type);
		}
	},
	'addContext':function(attribute, context)
	{
		if(!this.attributes[attribute])
		{
			for(var i = 0; i < this.attributes[attribute].contexts; i++)
			{
				if(this.attributes[attribute].contexts == context)
				{
					return;
				}
			}
			this.attributes[attribute].contexts[this.attributes[attribute].contexts.length] = context;
			this.attributes[attribute].contexts.sort();
		}
	},
	'removeContext':function(attribute, context)
	{
		
	}
};
org.bambuscms.app.persons.gui = {
	'createAttribute':function(attribute, type)
	{
		var els = $('org_bambuscms_app_persons_gui').getElementsByTagName('div');
		var insert = org.bambuscms.app.persons.gui.attributeHTML(attribute, type);
		if(els.length > 0)
		{
			var i = 0;
			while(els[i].nodeValue < attribute)
			{
				i++;
			}
			if(!els[i].nextSibling)
			{
				$('org_bambuscms_app_persons_gui').appendChild(insert);
			}
			else
			{
				$('org_bambuscms_app_persons_gui').insertBefore(insert,els[i].nextSibling)
			}
		}
		else
		{
			$('org_bambuscms_app_persons_gui').appendChild(insert);
		}
	},
	'attributeHTML':function(attribute, type)
	{
		var box = document.createElement('div');
		box.className = 'PersonAttributeBlock';
		var h3 = document.createElement('h3');
		h3.appendChild(document.createTextNode(attribute));
		box.appendChild(h3);
		return box;
	}
};
(function(){
	org.bambuscms.autorun.register(function(){
		for(att in org.bambuscms.app.persons.attributes)
		{
			org.bambuscms.app.persons.controller.addAttribute(att, org.bambuscms.app.persons.attributes[att].type);
		}
	});
})();