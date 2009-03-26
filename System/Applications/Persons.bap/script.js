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
			'type':2
		},
		'email':{
			'contexts':['arbeit', 'mobil', 'privat'],
			'entries':[
		           [1, 'test@mobil.de'],
		           [0, 'test@arbeit']
            ],
			'type':1
		},
		'instant_messenger':{
			'contexts':['aim', 'jabber', 'icq'],
			'entries':[
		           [1, 'test@jabber1'],
		           [1, 'test@jabber2']
            ],
			'type':0
		},
		'web_address':{
			'contexts':['arbeit', 'privat'],
			'entries':[],
			'type':0
		},
		'address':{
			'contexts':['arbeit', 'privat'],
			'entries':[],
			'type':3
		},
		'miscellaneous':{
			'contexts':['arbeit', 'privat'],
			'entries':[],
			'type':3
		}
	},
	'types':['text', 'email', 'phone', 'textbox'],
	'trim':{'email':1, 'phone':1},
	'replace':{ /* replace regexp operator with new regexp() */
		'phone':[/([^0-9\-\/\*\.\+]|[\s])+/g, ' ']
	},
	'check':{
	}
};
org.bambuscms.app.persons.person = function(data)
{
	this._data = data;
	this._gui = null;
	
	this._checkValue = function(attribute, index, restoreValue)
	{
		var r;
		var type = this.getAttributeType(attribute);
		var value = this._data.attributes[attribute].entries[index][1];
		if(this._data.trim[type])
		{
			value = value.replace(/(^[\s]*|[\s]*$)/g, '');
		}
		if(this._data.replace[type])
		{
			r = new RegExp(this._data.replace[type][0], 'g');
			value = value.replace(r, this._data.replace[type][1]);
		}
		if(this._data.check[attribute])
		{
			var att = this._gui.getAttributeNode(attribute);
			var entry;
			if(att)
			{
				entry = att.getEntryNode(index);
				if(entry)
				{
					r = new RegExp(this._data.check[attribute], 'i');
					entry.setWarning(r.exec(value) == null);
				}
			}
		}
		this._data.attributes[attribute].entries[index][1] = value;
	};
	
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
		if(!contexts || typeof contexts != 'Array')
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
		if(!attribute || !this._data.attributes[attribute])
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
		this._data.attributes[attribute].contexts = this._data.attributes[attribute].contexts;
	};
	
	//add new entry
	this.addAttributeEntry = function(attribute, context, value)
	{
		value = value || '';
		if(!attribute || !this._data.attributes[attribute])
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
		return this._data.attributes[attribute].contexts[this._data.attributes[attribute].entries[index][0]];
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
		var oldVal = this._data.attributes[attribute].entries[index][1];
		this._data.attributes[attribute].entries[index][1] = newValue;
		this._checkValue(attribute, index, oldVal);
		return this._data.attributes[attribute].entries[index][1];
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
	this.buildGUI = function()
	{
		//build gui html
		this._gui = new org.bambuscms.app.persons.gui.person(this);
		//for all attributes
		for (att in this._data.attributes)
		{
			//for all entries in attribute
			for (var i = 0; i < this._data.attributes[att].entries.length; i++)
			{	
				//check
				this._checkValue(
					att,
					i,
					this._data.attributes[att].entries[i][1]
				);
			}
		}
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
		if(newContext.substr(0,2) == '::')
		{
			var func = newContext.substr(2, newContext.length);
			this[func]();
		}
		else
		{
			this.context = this.controller.changeAttibuteContextAtIndex(this.attributeName, this.nr, newContext);
			for(var i = 0; i < this.select.options.length; i++)
			{
				this.select.options[i].selected = this.select.options[i].value == this.context;
			}
		}
	}
	
	this.updateValue = function(newValue)
	{
		this.value = this.controller.changeAttibuteValueAtIndex(this.attributeName, this.nr, newValue);
		this.data.value = this.value;
	}
	
	this.newContext = function()
	{
		var newContext = prompt(_('new_context'));
		if(newContext && newContext.length)
		{
			this.controller.addAttributeContext(this.attributeName, newContext);
			this.controller.changeAttibuteContextAtIndex(this.attributeName, this.nr, newContext);
			this.controller.buildGUI();
		}
	}
	
	this.remove = function()
	{
		this.controller.removeAttributeEntry(this.attributeName, this.nr);
		this.controller.buildGUI();
	}
	
	//DOM select
	this.select = $c('select');
	this.select.id = id+'_c';
	var contexts = controller.getAttributeContexts(attributeName);
	var grp = $c('optgroup');
	grp.label = _('contexts');
	for(var i = 0; i < contexts.length; i++)
	{
		var opt = $c('option');
		opt.appendChild($t(contexts[i]));
		opt.selected = contexts[i] == this.context;
		grp.appendChild(opt);
	}
	this.select.appendChild(grp);
	
	//actions
	var grp = $c('optgroup');
	grp.label = _('actions');
	var opt = $c('option');
	opt.appendChild($t(_('new_context')));
	opt.value = '::newContext';
	grp.appendChild(opt);
	this.select.appendChild(grp);

	
	this.select.onchange = function(){
		self.updateContext(this.options[this.selectedIndex].value);
	};
	
	this.setWarning = function(YN)
	{
		this.container.className = (YN)
			? 'persons_gui_entry person_gui_warning'
			: 'persons_gui_entry';
	}
	
	//DOM input
	var type = this.controller.getAttributeType(this.attributeName);
	switch(type)
	{
		case 'textbox':
			this.data = $c('textarea');
			break;
		case 'email': 
		case 'text':
		default:
			this.data = $c('input');
			this.data.type = 'text';
	}
	
	this.data.className = 'person_gui_entry_'+type;
	this.data.value = this.value;
	this.data.id = id+'_v';
	this.data.onchange = function(){
		self.updateValue(this.value);
	};
	this.data.onblur = function(){
		self.updateValue(this.value);
	};
	this.data.onfocus = function(){
		self.setWarning(false);
	};
	
	//DOM remove button
	this.button = $c('a');
	this.button.className = 'persons_gui_button persons_gui_button_remove';
	this.button.title = _('remove_entry');
	this.button.appendChild($t(' - '))
	this.button.onclick = function(){
		self.remove();
	};	
	
	//DOM container
	this.container = $c('div');
	this.container.className = 'persons_gui_entry';
	this.container.appendChild(this.select);
	this.container.appendChild(this.data);
	this.container.appendChild(this.button);
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
	head.appendChild($t(_(attributeName)));
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
	this.addButton.className = 'persons_gui_button persons_gui_button_add';
	this.addButton.title = _('add_entry');
	this.addButton.onclick = function(){self.addNewEntry(); return false;};
	this.addButton.appendChild($t(' + '));
	this.node.appendChild(this.addButton);
	
	this.floatStopper = $c('div');
	this.floatStopper.className = 'person_gui_floatStopper';
	this.node.appendChild(this.floatStopper);

	this.addNewEntry = function()
	{
		var contexts = this.controller.getAttributeContexts(this.attributeName);
		var i = this.controller.addAttributeEntry(this.attributeName, contexts[0], '');
		this.children[i] = new org.bambuscms.app.persons.gui.entry(controller, this.attributeName, [contexts[0], ''], i, this.node.id+'_'+i);
		this.node.insertBefore(this.children[i].getNode(), this.addButton);
	}
	
	this.getEntryNode = function(index)
	{
		return this.children[index] ? this.children[index] : null;
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
	this.getAttributeNode = function(attributeName)
	{
		for(var i = 0; i < this.children.length; i++)
		{
			if(this.children[i].attributeName == attributeName)
			{
				return this.children[i]
			}
		}
		return null;
	};
};

var p;

org.bambuscms.app.persons.handler = function(object)
{
	p = new org.bambuscms.app.persons.person(object);
	p.buildGUI();
}

org.bambuscms.autorun.register(function(){
	org.bambuscms.http.fetchJSONObject(
		org.bambuscms.http.managementRequestURL({
			'controller':org.bambuscms.app.controller,
			'call':'getPersonData',
			'edit':$('alias').value
		}),
		org.bambuscms.app.persons.handler
	);
});

	









