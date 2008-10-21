org.bambuscms.wpropertyeditor = 
{
	//items are registered here
	'_items':[],
	
	//add functions to controls
	'_init':function()
	{
		for(var i = 0; i < org.bambuscms.wpropertyeditor._items.length; i++)
		{
			var item = org.bambuscms.wpropertyeditor._items[i];
			org.bambuscms.gui.setEventHandler($('WPropertyEditor_'+item+'_mv_up'), 'click', function(){org.bambuscms.wpropertyeditor.up(item);});
			org.bambuscms.gui.setEventHandler($('WPropertyEditor_'+item+'_mv_down'), 'click', function(){org.bambuscms.wpropertyeditor.down(item);});
			org.bambuscms.gui.setEventHandler($('WPropertyEditor_'+item+'_selector'), 'change', function(){org.bambuscms.wpropertyeditor.selectProperty(item);});
			for(var x = 0; x < $('WPropertyEditor_'+item+'_selector').length; x++)
			{
				var att = $('WPropertyEditor_'+item+'_selector').options[x].value;
				org.bambuscms.gui.setEventHandler($('WPropertyEditor_'+item+'_option_'+att+'_active'), 'change', function(){org.bambuscms.wpropertyeditor.changeActiveStatus(item, this.checked);});
			}
		}
	},
	
	//register item
	'init':function(id)
	{
		org.bambuscms.wpropertyeditor._items.push(id);
		org.bambuscms.autorun.register(org.bambuscms.wpropertyeditor._init);
		org.bambuscms.wpropertyeditor.init = function(_id){org.bambuscms.wpropertyeditor._items.push(_id);};
	},
	
	//move property up in list
	'up':function(id)
	{
	throw new Error('not implemented');
		//exchange selected element with the upper
		org.bambuscms.wpropertyeditor.indexList(id);
	},
	
	//move property down in list
	'down':function(id)
	{
	throw new Error('not implemented');
		//exchange selected element with the lower
		org.bambuscms.wpropertyeditor.indexList(id);
	},
	
	//show options for selected property
	'selectProperty':function(id)
	{
		//hide all div.WPropertyEditor_option
		var elms = $('WPropertyEditor_'+id+'_options').getElementsByTagName('div');
		for(var i = 0; i < elms.length; i++)
		{
			if(elms[i].className == 'WPropertyEditor_option')
			{
				elms[i].style.display = 'none';
			}
		}
		//show div matching select.selectedIndex
		var select = $('WPropertyEditor_'+item+'_selector').options[$('WPropertyEditor_'+item+'_selector').selectedIndex].value;
		$('WPropertyEditor_'+id+'_option_'+select+'_data').style.display = 'block';
	},
	
	//change status for selected property (active/inactive)
	'changeActiveStatus':function(id, stat)
	{
		//change class of element at selectedIndex
		$('WPropertyEditor_'+item+'_selector').options[$('WPropertyEditor_'+item+'_selector').selectedIndex].className = stat ? 'active' : 'inactive';
	},
	
	//save the position of properties in hidden inputs
	'indexList':function(id)
	{
		//loop select: fill hidden inputs with pos
		for(var x = 0; x < $('WPropertyEditor_'+item+'_selector').length; x++)
		{
			var att = $('WPropertyEditor_'+item+'_selector').options[x].value;
			$('WPropertyEditor_'+item+'_'+att+'_position').value = x+1;
		}
	}
};