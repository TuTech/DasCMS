org.bambuscms.wtagpermissions = {
	'_select':function(check){
		var sub = $('View_UIElement_TagPermissions').getElementsByTagName('input');
		for(var i = 0; i < sub.length; i++)
		{
			if(sub[i].type == 'checkbox')
			{
				sub[i].checked = check;
			}
		}
	},
	'selectAll':function(){
		org.bambuscms.wtagpermissions._select(true);
	},
	'selectNone':function(){
		org.bambuscms.wtagpermissions._select(false);
	}
};