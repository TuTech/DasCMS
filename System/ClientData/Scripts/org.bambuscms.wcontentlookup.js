org.bambuscms.wcontentlookup = {};
org.bambuscms.wcontentlookup.filter = function()
{
	var filterStr
	if($('WContentLookupFilter') && $('WContentLookup'))
	{
	
		filterStr = $('WContentLookupFilter').value;
		var elements = $('WContentLookup').getElementsByTagName('option');
		var str;
		for(var i = 0; i < elements.length; i++)
		{
			str = elements[i].text;
			elements[i].style.display = (str.indexOf(filterStr) > -1) ? 'block' : 'none';
		}
	}
}
