function Create()
{
	input = $c('input');
	input.setAttribute('name','create');
	input.setAttribute('type','text');
	input.setAttribute('value','');
		
	org.bambuscms.app.dialog.create(_('create_new_template'), _('name_of_new_template'), input, _('ok'), _('cancel'));
	org.bambuscms.app.dialog.setAction('create');
	input.focus();
}
function Delete()
{
	input = $c('input');
	input.setAttribute('name','delete');
	input.setAttribute('type','hidden');
	input.setAttribute('value','yes');
		
	org.bambuscms.app.dialog.create(_('delete_template'), _('do_you_really_want_to_delete_this_text'), input, _('yes'), _('no'));
	org.bambuscms.app.dialog.setAction('delete');
}

org.bambuscms.app.persons = {};
org.bambuscms.app.persons.testData = {
	'attributes':{
		'phone':{
			'contexts':['arbeit', 'mobil', 'privat', 'fax arbeit', 'fax privat'],
			'entries':[
		           [0, '+49 01054 2540 4521'],
		           [1, '23904 555-7530']
            ],
			'type':0
		},
		'im':{
			'contexts':['aim', 'jabber', 'icq'],
			'entries':[
		           [1, 'test@jabber1'],
		           [1, 'test@jabber2']
            ],
			'type':0
		},
		'email':{
			'contexts':['arbeit', 'mobil', 'privat'],
			'entries':[
		           [1, 'test@mobil'],
		           [0, 'test@arbeit']
            ],
			'type':1
		}
	},
	'types':['text', 'email']
};
org.bambuscms.app.persons.person = function(data)
{
	this._data = data;
	
	// array of type strings
	this.getTypes = function()
	{
		return this._data.types;
	};
	
	// array of attributes
	this.getAttributes = function()
	{
		var atts = [];
		for(att in this._data.attributes)
			atts[atts.length] = att;
		atts = atts.sort();
		return atts;
	};
	
	// type string for attribute
	this.getAttributeType = function(attribute)
	{
		if(!this._data.attributes[attribute])
		{
			throw 'not an attribute';
		}
		return this._data.types[this._data.attributes[attribute].type];
	};
	
	// array of context strings
	this.getAttributeContexts = function(attribute)
	{
		if(!this._data.attributes[attribute])
		{
			throw 'not an attribute';
		}
		return this._data.attributes[attribute].contexts;
	};
	
	// array of attribute arrays [[ctx, val],...]
	this.getAttributeEntries = function(attribute)
	{
		if(!this._data.attributes[attribute])
		{
			throw 'not an attribute';
		}
		var res = [];
		for (var i = 0; i < this._data.attributes[attribute].entries.length; i++)
		{
			res[res.length] = [
			    this._data.attributes[attribute].contexts[this._data.attributes[attribute].entries[i][0]],
			    this._data.attributes[attribute].entries[i][1]
			];
		}
		return res;
	};
	
	// create new attribute with type string and context array
	this.createAttribute = function(attribute, type, contexts)
	{
		// attribute check
		if(!attribute)
		{
			throw 'no attribute given';
		}
		if(this._data.attributes[attribute])
		{
			throw 'attribute already exists';
		}
		// type check
		var typeIndex = this._validateType(type);
		if(typeIndex == -1)
		{
			throw 'invalid type';
		}
		// init optional contexts
		if(!contexts || typeof contexts != 'Array')
			contexts = [];
		
		// create att
		this._data.attributes[attribute] = {
			'contexts':contexts,
			'entries':[],
			'type':typeIndex
		};
	};
	
	//add context to attribute
	this.addAttributeContext = function(attribute, context)
	{
		if(!attribute || !this._data.attributes[attribute])
		{
			throw 'invalid attribute';
		}
		if(!context)
		{
			throw 'no context given';
		}
		for (var i = 0; i < this._data.attributes[attribute].contexts.length; i++)
		{
			if(this._data.attributes[attribute].contexts[i] == context)
				return;//already exists
		}
		this._data.attributes[attribute].contexts[this._data.attributes[attribute].contexts.length] = context;
		this._data.attributes[attribute].contexts = this._data.attributes[attribute].contexts.sort();
	};
	
	//add new entry
	this.addAttributeEntry = function(attribute, context, value)
	{
		value = value || '';
		if(!attribute || !this._data.attributes[attribute])
		{
			throw 'invalid attribute';
		}
		if(!context)
		{
			throw 'no context given';
		}
		// context check
		var contextIndex = this._validateAttributeContext(attribute, context);
		if(contextIndex == -1)
		{
			throw 'invalid context';
		}
		var index = this._data.attributes[attribute].entries.length;
		this._data.attributes[attribute].entries[index] = [contextIndex, value];
		return index;
	}
	
	this.changeAttibuteContextAtIndex = function(attribute, index, newContext)
	{
		if(!this._data.attributes[attribute])
		{
			throw 'invalid attribute';
		}
		if(!this._data.attributes[attribute].entries[index])
		{
			throw 'no entry at index';
		}
		var contextIndex = this._validateAttributeContext(attribute, newContext);
		if(contextIndex == -1)
		{
			throw 'invalid context';
		}
		this._data.attributes[attribute].entries[index][0] = contextIndex;
	}
	
	this.changeAttibuteValueAtIndex = function(attribute, index, newValue)
	{
		if(!this._data.attributes[attribute])
		{
			throw 'invalid attribute';
		}
		if(!this._data.attributes[attribute].entries[index])
		{
			throw 'no entry at index';
		}
		this._data.attributes[attribute].entries[index][1] = newValue;
	}
	
	//removeentry at index
	this.removeAttributeEntry = function(attribute, index)
	{
		var atts = [];
		for(var i = 0; i < this._data.attributes[attribute].entries.length; i++)
		{
			if(index != i)
			{
				atts[atts.length] = this._data.attributes[attribute].entries[i];
			}
		}
		this._data.attributes[attribute].entries = atts;
	}
	
	this.removeAttributeContext = function(attribute, context){};
	
	this.removeAttribute = function(attribute){};
	
	this._validateAttributeContext = function(attribute, context)
	{
		var index = -1;
		if(attribute && this._data.attributes[attribute])
		{
			index = this._validate(this._data.attributes[attribute].contexts, context);
		}
		return index;
	}
	
	this._validateType = function(type)
	{
		return this._validate(this._data.types, type);
	}
	
	this._validate = function(array, key)
	{
		var index = -1;
		for(var i = 0; i < array.length; i++)
		{
			if(array[i] == key)
			{
				index = i;
			}
		}
		return index;
	}
	
	//dump data in alert box
	this.debug = function()
	{
		var str = '';
		var a;
		for(att in this._data.attributes)
		{
			str += att+' ['+this._data.types[this._data.attributes[att].type]+']\n    (';
			str += this._data.attributes[att].contexts.join(', ');
			str += ')\n    data:\n';
			for(var i = 0; i < this._data.attributes[att].entries.length; i++)
			{
				a = this._data.attributes[att].entries[i];
				str += '        '+this._data.attributes[att].contexts[a[0]];
				str += ': '+a[1]+'\n';
			}
			str += '\n';
		}
		alert(str);
	};
};
org.bambuscms.app.persons.gui = {};

org.bambuscms.app.persons.gui.entry = function(controller, attributeName, entry, nr, id)
{
	//init
	this.controller = controller;
	this.nr = nr;
	this.attributeName = attributeName;
	this.context = entry[0];
	this.value = entry[1];
	this.id = id;
	var self = this;
	
	//methods
	this.getNode = function()
	{
		return this.container;
	}
	
	this.updateContext = function(newContext)
	{
		this.context = newContext;
		this.controller.changeAttibuteContextAtIndex(this.attributeName, this.nr, newContext);
	}
	
	this.updateValue = function(newValue)
	{
		this.value = newValue;
		this.controller.changeAttibuteValueAtIndex(this.attributeName, this.nr, newValue);
	}
	
	//DOM select
	this.select = $c('select');
	this.select.id = id+'_c';
	var contexts = controller.getAttributeContexts(attributeName);
	for(var i = 0; i < contexts.length; i++)
	{
		var opt = $c('option');
		opt.appendChild($t(contexts[i]));
		opt.selected = contexts[i] == this.context;
		this.select.appendChild(opt);
	}
	this.select.onchange = function(){
		self.updateContext(this.options[this.selectedIndex].value);
	};
	
	//DOM input
	this.data = $c('input');
	this.data.type = 'text';
	this.data.value = this.value;
	this.data.id = id+'_v';
	this.data.onchange = function(){
		self.updateValue(this.value);
	};
	
	//DOM container
	this.container = $c('div');
	this.container.className = 'persons_gui_entry';
	this.container.appendChild(this.select);
	this.container.appendChild(this.data);
	
};

org.bambuscms.app.persons.gui.attribute = function(controller, attributeName, id)
{
	//init
	this.controller = controller;
	this.attributeName = attributeName;
	this.id = id;
	var self = this;
	//DOM
	this.node = $c('div');
	this.node.id = id;
	this.node.className = 'persons_gui_attribute';
	var head = $c('h3');
	head.appendChild($t(attributeName));
	this.node.appendChild(head);
	
	//generate entries
	this.children = [];
	var chlds = controller.getAttributeEntries(attributeName);
	for(var i = 0; i < chlds.length; i++)
	{
		this.children[i] = new org.bambuscms.app.persons.gui.entry(controller, attributeName, chlds[i], i, this.node.id+'_'+i);
		this.node.appendChild(this.children[i].getNode());
	}
	
	//methods
	this.getNode = function()
	{
		return this.node;
	}

	//DOM Button
	this.addButton = $c('a');
	this.addButton.onclick = function(){self.addNewEntry(); return false;};
	this.addButton.appendChild($t('add'));
	this.node.appendChild(this.addButton);
	
	this.addNewEntry = function()
	{
		var contexts = this.controller.getAttributeContexts(this.attributeName);
		var i = this.controller.addAttributeEntry(this.attributeName, contexts[0], '');
		this.children[i] = new org.bambuscms.app.persons.gui.entry(controller, this.attributeName, [contexts[0], ''], i, this.node.id+'_'+i);
		this.node.insertBefore(this.children[i].getNode(), this.addButton);
		
	}
	
};

org.bambuscms.app.persons.gui.person = function(controller)
{
	//init
	this.controller = controller;
	
	//DOM
	this.node = $c('div');
	this.node.id = 'Attributes';
	$('org_bambuscms_app_persons_gui').innerHTML = '';
	$('org_bambuscms_app_persons_gui').appendChild(this.node);

	//generate attributes
	this.children = [];
	var chlds = controller.getAttributes();
	for(var i = 0; i < chlds.length; i++)
	{
		this.children[i] = new org.bambuscms.app.persons.gui.attribute(controller, chlds[i], this.node.id+'_'+i);
		this.node.appendChild(this.children[i].getNode());
	}
};



var p = new org.bambuscms.app.persons.person(org.bambuscms.app.persons.testData);

//FIXME if remove attribute: regenerate (gui = new org.bambuscms.app.persons.gui.person(p);)

org.bambuscms.autorun.register(function(){
//	p.debug();

	var gui = new org.bambuscms.app.persons.gui.person(p);
});








