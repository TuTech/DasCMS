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
			org.bambuscms.gui.setEventHandler($('View_UIElement_PropertyEditor_'+item+'_mv_up'), 'click', function(){org.bambuscms.wpropertyeditor.up(org.bambuscms.wpropertyeditor._getItem(this));});
			org.bambuscms.gui.setEventHandler($('View_UIElement_PropertyEditor_'+item+'_mv_down'), 'click', function(){org.bambuscms.wpropertyeditor.down(org.bambuscms.wpropertyeditor._getItem(this));});
			org.bambuscms.gui.setEventHandler($('View_UIElement_PropertyEditor_'+item+'_selector'), 'change', function(){org.bambuscms.wpropertyeditor.selectProperty(org.bambuscms.wpropertyeditor._getItem(this));});
			for(var x = 0; x < $('View_UIElement_PropertyEditor_'+item+'_selector').length; x++)
			{
				var att = $('View_UIElement_PropertyEditor_'+item+'_selector').options[x].value;
				var fx = function(){org.bambuscms.wpropertyeditor.changeActiveStatus(org.bambuscms.wpropertyeditor._getItem(this), this.checked);};
				org.bambuscms.gui.setEventHandler($('View_UIElement_PropertyEditor_'+item+'_option_'+att+'_active'), 'click', fx);
				org.bambuscms.gui.setEventHandler($('View_UIElement_PropertyEditor_'+item+'_option_'+att+'_active'), 'change', fx);
			}
			if($('View_UIElement_PropertyEditor_'+item+'_selector').options.length)
			{
				$('View_UIElement_PropertyEditor_'+item+'_selector').selectedIndex = 0;
				org.bambuscms.wpropertyeditor.selectProperty(item);
			}
		}
	},
	
	'_getItem': function(self)
	{
		var name = self.id.split("_");
		return name[3];
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
		var sel = $('View_UIElement_PropertyEditor_'+id+'_selector');
		var i = sel.selectedIndex;
		if(i > 0)
		{
			var cur = [sel.options[i].text, sel.options[i].value];
			var prec = [sel.options[i-1].text, sel.options[i-1].value];

			sel.options[i].text = prec[0];
			sel.options[i].value = prec[1];

			sel.options[i-1].text = cur[0];
			sel.options[i-1].value = cur[1];
			
			sel.selectedIndex = i-1;
		}
		org.bambuscms.wpropertyeditor.indexList(id);
	},
	
	//move property down in list
	'down':function(id)
	{
		var sel = $('View_UIElement_PropertyEditor_'+id+'_selector');
		var i = sel.selectedIndex;
		if(i < sel.options.length-1)
		{
			var cur = [sel.options[i].text, sel.options[i].value];
			var next = [sel.options[i+1].text, sel.options[i+1].value];

			sel.options[i].text = next[0];
			sel.options[i].value = next[1];

			sel.options[i+1].text = cur[0];
			sel.options[i+1].value = cur[1];
			
			sel.selectedIndex = i+1;
		}
		org.bambuscms.wpropertyeditor.indexList(id);
	},
	
	//show options for selected property
	'selectProperty':function(id)
	{
		//hide all div.View_UIElement_PropertyEditor_option
		var elms = $('View_UIElement_PropertyEditor_'+id+'_options').getElementsByTagName('div');
		for(var i = 0; i < elms.length; i++)
		{
			if(elms[i].className == 'View_UIElement_PropertyEditor_option')
			{
				elms[i].style.display = 'none';
			}
		}
		//show div matching select.selectedIndex
		var select = $('View_UIElement_PropertyEditor_'+id+'_selector').options[$('View_UIElement_PropertyEditor_'+id+'_selector').selectedIndex].value;
		$('View_UIElement_PropertyEditor_'+id+'_option_'+select+'_data').style.display = 'block';
	},
	
	//change status for selected property (active/inactive)
	'changeActiveStatus':function(id, stat)
	{
		//change class of element at selectedIndex
		$('View_UIElement_PropertyEditor_'+id+'_selector').options[$('View_UIElement_PropertyEditor_'+id+'_selector').selectedIndex].className = stat ? 'WPE_active' : 'WPE_inactive';
	},
	
	//save the position of properties in hidden inputs
	'indexList':function(id)
	{
		//loop select: fill hidden inputs with pos
		for(var x = 0; x < $('View_UIElement_PropertyEditor_'+id+'_selector').length; x++)
		{
			var att = $('View_UIElement_PropertyEditor_'+id+'_selector').options[x].value;
			$('View_UIElement_PropertyEditor_'+id+'_'+att+'_position').value = x+1;
		}
	}
};